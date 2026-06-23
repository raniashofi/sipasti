<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TiketTeknisi;
use App\Models\TimTeknis;
use App\Notifications\StatusTiketNotification;
use App\Notifications\TiketMasukNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengaduanSayaController extends Controller
{
    private const RATEABLE_STATUSES = ['selesai', 'rusak_berat', 'tiket_ditutup'];
    private const REOPENABLE_STATUS = 'selesai';
    private const EDITABLE_STATUS = 'perlu_revisi';

    public function index(Request $request)
    {
        $opd = Auth::user()->opd;
        if (!$opd) {
            abort(403, 'Data OPD tidak ditemukan.');
        }

        $query = Tiket::where('opd_id', $opd->id)
            ->with(['latestStatus', 'chatRooms'])
            ->orderByDesc('created_at');

        // Filter status — "selesai" mencakup tiket_ditutup karena keduanya ditampilkan sama ke OPD
        if ($request->filled('status')) {
            $statuses = $request->status === 'selesai'
                ? ['selesai', 'tiket_ditutup']
                : [$request->status];
            $query->whereHas('latestStatus', fn($q) =>
                $q->whereIn('status_tiket', $statuses)
            );
        }

        // Search by ID or subjek
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) =>
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('subjek_masalah', 'like', "%{$search}%")
            );
        }

        $tikets = $query->paginate(10)->withQueryString();
        $this->attachUnreadChatCounts($tikets->getCollection(), Auth::id());

        return view('opd.pengaduan-saya.index', compact('tikets'));
    }

    public function show(string $id)
    {
        $opd = Auth::user()->opd;
        if (!$opd) {
            abort(404);
        }
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with([
                          'kb',
                          'latestStatus',
                          'chatRooms',
                          'buktiFoto',
                          'statusTiket' => fn($q) => $q->orderBy('created_at', 'asc'),
                      ])
                      ->findOrFail($id);

        // ✅ Check if ticket has ever been reopened (dibuka_kembali in history)
        $sudahPernahDibukakembali = $tiket->statusTiket
            ->where('status_tiket', 'dibuka_kembali')
            ->isNotEmpty();

        $this->attachUnreadChatCounts(collect([$tiket]), Auth::id());

        return view('opd.pengaduan-saya.detail', compact('tiket', 'sudahPernahDibukakembali'));
    }

    private function attachUnreadChatCounts($tikets, string $userId): void
    {
        $roomIds = $tikets
            ->flatMap(fn($tiket) => $tiket->chatRooms ?? collect())
            ->whereIn('nama_roomchat', ['admin', 'teknis'])
            ->pluck('id')
            ->values();

        $unreadMap = collect();
        if ($roomIds->isNotEmpty()) {
            $unreadMap = DB::table('chat_messages as m')
                ->select('m.room_id', DB::raw('COUNT(*) as count'))
                ->whereIn('m.room_id', $roomIds)
                ->where('m.sender_id', '!=', $userId)
                ->whereRaw("m.created_at > COALESCE(
                    (SELECT cru.last_read_at FROM chat_room_users cru
                     WHERE cru.room_id = m.room_id AND cru.user_id = ?),
                    '1970-01-01 00:00:00'
                )", [$userId])
                ->groupBy('m.room_id')
                ->pluck('count', 'room_id');
        }

        $tikets->each(function ($tiket) use ($unreadMap) {
            $adminRoom = ($tiket->chatRooms ?? collect())->firstWhere('nama_roomchat', 'admin');
            $teknisRoom = ($tiket->chatRooms ?? collect())->firstWhere('nama_roomchat', 'teknis');

            $tiket->admin_room_id = $adminRoom?->id;
            $tiket->teknis_room_id = $teknisRoom?->id;
            $tiket->admin_unread_count = $adminRoom ? (int) ($unreadMap->get($adminRoom->id, 0)) : 0;
            $tiket->teknis_unread_count = $teknisRoom ? (int) ($unreadMap->get($teknisRoom->id, 0)) : 0;
            $tiket->unread_count = $tiket->admin_unread_count + $tiket->teknis_unread_count;
        });
    }

    public function chat(string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::with('buktiFoto')->where('opd_id', $opd->id)->findOrFail($id);

        return view('opd.pengaduan-saya.chat', compact('tiket'));
    }

    /**
     * OPD mengkonfirmasi tiket sudah selesai + kirim penilaian.
     */
    public function konfirm(Request $request, string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with(['latestStatus', 'buktiFoto'])
                      ->findOrFail($id);

        if (!in_array($tiket->latestStatus?->status_tiket, $this->rateableStatuses(), true)) {
            return back()->with('error', 'Tiket tidak dalam status yang dapat dinilai.');
        }

        $request->validate([
            'penilaian' => 'required|integer|min:1|max:5',
        ]);

        $tiket->update([
            'penilaian' => $request->input('penilaian'),
        ]);

        // Kasus 3: OPD konfirm → tutup tiket (jika belum tiket_ditutup)
        if ($tiket->latestStatus?->status_tiket !== 'tiket_ditutup') {
            StatusTiket::create([
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'tiket_ditutup',
                'catatan'      => 'Tiket dikonfirmasi selesai oleh OPD.',
                'created_at'   => now(),
            ]);
        }

        return redirect()->route('opd.tiket.index')
            ->with('success', 'Penilaian untuk tiket #' . $id . ' telah dikirim. Terima kasih!');
    }

    /**
     * OPD membuka kembali tiket yang sudah selesai karena masalah belum teratasi.
     * - SLA akan direset dari waktu pembukaan ulang ini
     * - Max 3x pembukaan total: 1x pengajuan awal + 2x pembukaan ulang
     * - Jika diselesaikan oleh Admin Helpdesk → kembali ke panduan_remote
     * - Jika diselesaikan oleh Tim Teknis     → kembali ke dibuka_kembali
     */
    public function bukaKembali(Request $request, string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with(['latestStatus', 'statusTiket'])
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== $this->reopenableStatus()) {
            return back()->with('error', 'Tiket tidak dalam status selesai.');
        }

        // Cek apakah masih bisa dibuka kembali (max 3x: 1 awal + 2 ulang)
        if (!$tiket->canBeReopened()) {
            return back()->with('error', 'Tiket ini sudah mencapai batas maksimal pembukaan (' .
                              $tiket->reopened_count . 'x). Silakan buat tiket baru untuk melaporkan masalah.');
        }

        $latestSelesai = $tiket->statusTiket->where('status_tiket', 'selesai')->sortByDesc('created_at')->first();

        $request->validate([
            'alasan'     => 'required|string|max:1000',
            'file_bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'file_bukti.max' => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
        ]);

        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')->store('tiket/bukti', 'public');
        }

        // Mark tiket sebagai dibuka kembali (increment reopened_count & set last_reopened_at)
        try {
            $tiket->markAsReopened();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $resolvedByAdmin = str_starts_with($latestSelesai?->catatan ?? '', '[Diselesaikan oleh Admin Helpdesk]');

        if ($resolvedByAdmin) {
            // Kembalikan ke Admin Helpdesk (panduan remote)
            StatusTiket::create([
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'panduan_remote',
                'catatan'      => '[Dibuka Kembali oleh OPD - Pembukaan Ulang ke-' . $tiket->reopened_count . '] ' . $request->input('alasan'),
                'file_bukti'   => $filePath,
                'created_at'   => now(),
            ]);

            // Notifikasi ke Admin Helpdesk pemilik tiket
            $tiket->load('admin.user');
            $tiket->admin?->user?->notify(new TiketMasukNotification(
                kodeTiket : $tiket->id,
                namaOpd   : $tiket->opd?->nama_opd ?? 'OPD',
                url       : route('admin_helpdesk.tiket.panduan'),
            ));

            return redirect()->route('opd.tiket.show', $id)
                ->with('success', 'Tiket telah dibuka kembali (Pembukaan Ulang ke-' . $tiket->reopened_count . '). Admin Helpdesk akan segera menangani kendala yang Anda laporkan. SLA akan dihitung ulang dari sekarang.');
        }

        // Kembalikan ke Tim Teknis
        StatusTiket::create([
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'dibuka_kembali',
            'catatan'      => '[Pembukaan Ulang ke-' . $tiket->reopened_count . '] ' . $request->input('alasan'),
            'file_bukti'   => $filePath,
            'created_at'   => now(),
        ]);

        // Reaktifkan baris pivot lama karena tiket_id + teknis_id adalah primary key gabungan.
        $penugasanSelesai = TiketTeknisi::where('tiket_id', $tiket->id)
            ->where('status_tugas', 'selesai')
            ->get()
            ->unique(fn($row) => $row->teknis_id . '|' . $row->peran_teknisi);

        foreach ($penugasanSelesai as $penugasan) {
            $sudahAktif = TiketTeknisi::where('tiket_id', $tiket->id)
                ->where('teknis_id', $penugasan->teknis_id)
                ->where('status_tugas', 'aktif')
                ->exists();

            if (! $sudahAktif) {
                TiketTeknisi::where('tiket_id', $tiket->id)
                    ->where('teknis_id', $penugasan->teknis_id)
                    ->update([
                        'peran_teknisi'       => $penugasan->peran_teknisi,
                        'waktu_ditugaskan'    => now(),
                        'status_tugas'        => 'aktif',
                        'alasan_dikembalikan' => null,
                    ]);
            }
        }

        // Notifikasi ke semua Tim Teknis yang pernah ditugaskan di tiket ini
        $teknisiIds = TiketTeknisi::where('tiket_id', $tiket->id)->pluck('teknis_id');
        TimTeknis::with('user')->whereIn('id', $teknisiIds)->get()
            ->each(fn ($t) => $t->user?->notify(new StatusTiketNotification(
                kodeTiket  : $tiket->id,
                status     : 'sedang_ditangani',
                keterangan : 'OPD membuka tiket kembali (Pembukaan Ulang ke-' . $tiket->reopened_count . '). SLA akan dihitung ulang dari sekarang.',
                url        : route('tim_teknis.antrean'),
            )));

        return redirect()->route('opd.tiket.show', $id)
            ->with('success', 'Tiket telah dibuka kembali (Pembukaan Ulang ke-' . $tiket->reopened_count . '). SLA akan dihitung ulang dari sekarang. Tim Teknis akan segera menangani kendala Anda.');
    }

    /**
     * Tampilkan form edit tiket (hanya saat status perlu_revisi).
     */
    public function edit(string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with(['latestStatus', 'buktiFoto'])
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== $this->editableStatus()) {
            return redirect()->route('opd.tiket.show', $id)
                ->with('error', 'Tiket hanya dapat diedit saat berstatus Perlu Revisi.');
        }

        return view('opd.pengaduan-saya.edit', compact('tiket'));
    }

    /**
     * Simpan perubahan tiket dan kembalikan ke verifikasi_admin.
     */
    public function update(Request $request, string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with('latestStatus')
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== $this->editableStatus()) {
            return redirect()->route('opd.tiket.show', $id)
                ->with('error', 'Tiket hanya dapat diedit saat berstatus Perlu Revisi.');
        }

        $request->validate([
            'subjek_masalah' => 'required|string|max:255',
            'detail_masalah' => 'required|string',
            'foto_bukti'     => 'nullable|array|max:5',
            'foto_bukti.*'   => 'image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'foto_bukti.*.max' => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
        ]);

        // Hapus semua foto lama jika ada foto baru yang diunggah
        if ($request->hasFile('foto_bukti')) {
            // Hapus file lama dari storage
            $fotoBuktiLama = $tiket->buktiFoto()->get();
            foreach ($fotoBuktiLama as $foto) {
                Storage::disk('public')->delete($foto->foto_path);
                $foto->delete();
            }

            // Simpan foto baru
            foreach ($request->file('foto_bukti') as $foto) {
                $fotoPath = $foto->store('tiket/foto', 'public');
                \App\Models\TiketBuktiFoto::create([
                    'tiket_id'  => $tiket->id,
                    'foto_path' => $fotoPath,
                ]);
            }
        }

        $tiket->update([
            'subjek_masalah'        => $request->input('subjek_masalah'),
            'detail_masalah'        => $request->input('detail_masalah'),
            'spesifikasi_perangkat' => $request->input('spesifikasi_perangkat'),
            'lokasi'                => $request->input('lokasi'),
        ]);

        // Kembalikan status ke verifikasi_admin setelah revisi
        StatusTiket::create([
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'catatan'      => 'Tiket telah direvisi oleh OPD dan dikembalikan untuk verifikasi ulang.',
            'created_at'   => now(),
        ]);

        return redirect()->route('opd.tiket.show', $id)
            ->with('success', 'Tiket berhasil diperbarui dan dikirim kembali untuk verifikasi.');
    }

    protected function rateableStatuses(): array
    {
        return self::RATEABLE_STATUSES;
    }

    protected function reopenableStatus(): string
    {
        return self::REOPENABLE_STATUS;
    }

    protected function editableStatus(): string
    {
        return self::EDITABLE_STATUS;
    }
}
