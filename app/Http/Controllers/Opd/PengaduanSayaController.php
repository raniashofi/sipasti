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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengaduanSayaController extends Controller
{
    public function index(Request $request)
    {
        $opd = Auth::user()->opd;
        if (!$opd) {
            abort(403, 'Data OPD tidak ditemukan.');
        }

        $query = Tiket::where('opd_id', $opd->id)
            ->with('latestStatus')
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
                          'statusTiket' => fn($q) => $q->orderBy('created_at', 'asc'),
                      ])
                      ->findOrFail($id);

        return view('opd.pengaduan-saya.detail', compact('tiket'));
    }

    public function chat(string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)->findOrFail($id);

        return view('opd.pengaduan-saya.chat', compact('tiket'));
    }

    /**
     * OPD mengkonfirmasi tiket sudah selesai + kirim penilaian.
     */
    public function konfirm(Request $request, string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with('latestStatus')
                      ->findOrFail($id);

        if (!in_array($tiket->latestStatus?->status_tiket, ['selesai', 'rusak_berat', 'tiket_ditutup'])) {
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
                'id'           => 'STS-' . strtoupper(Str::random(10)),
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
     * - Jika diselesaikan oleh Admin Helpdesk → kembali ke panduan_remote
     * - Jika diselesaikan oleh Tim Teknis     → kembali ke dibuka_kembali
     */
    public function bukaKembali(Request $request, string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with(['latestStatus', 'statusTiket'])
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== 'selesai') {
            return back()->with('error', 'Tiket tidak dalam status selesai.');
        }

        // Cek sudah pernah dibuka kembali (oleh teknisi) ATAU kembali ke admin setelah selesai
        $latestSelesai  = $tiket->statusTiket->where('status_tiket', 'selesai')->sortByDesc('created_at')->first();
        $selesaiAt      = $latestSelesai?->created_at;
        $kembaliKeAdmin = $selesaiAt && $tiket->statusTiket
            ->where('status_tiket', 'panduan_remote')
            ->filter(fn($s) => $s->created_at > $selesaiAt)
            ->isNotEmpty();

        $sudahPernahDibukakembali = $tiket->statusTiket->where('status_tiket', 'dibuka_kembali')->isNotEmpty()
            || $kembaliKeAdmin;

        if ($sudahPernahDibukakembali) {
            return back()->with('error', 'Tiket ini sudah pernah dibuka kembali sebelumnya dan tidak dapat dibuka kembali lagi.');
        }

        $request->validate([
            'alasan'     => 'required|string|max:1000',
            'file_bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')->store('tiket/bukti', 'public');
        }

        $resolvedByAdmin = str_starts_with($latestSelesai?->catatan ?? '', '[Diselesaikan oleh Admin Helpdesk]');

        if ($resolvedByAdmin) {
            // Kembalikan ke Admin Helpdesk (panduan remote)
            StatusTiket::create([
                'id'           => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'panduan_remote',
                'catatan'      => '[Dibuka Kembali oleh OPD] ' . $request->input('alasan'),
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
                ->with('success', 'Tiket telah dibuka kembali. Admin Helpdesk akan segera menangani kendala yang Anda laporkan.');
        }

        // Kembalikan ke Tim Teknis
        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'dibuka_kembali',
            'catatan'      => $request->input('alasan'),
            'file_bukti'   => $filePath,
            'created_at'   => now(),
        ]);

        // Aktifkan kembali penugasan Tim Teknis agar tiket muncul di antrean mereka
        TiketTeknisi::where('tiket_id', $tiket->id)
            ->where('status_tugas', 'selesai')
            ->update(['status_tugas' => 'aktif']);

        // Notifikasi ke semua Tim Teknis yang pernah ditugaskan di tiket ini
        $teknisiIds = TiketTeknisi::where('tiket_id', $tiket->id)->pluck('teknis_id');
        TimTeknis::with('user')->whereIn('id', $teknisiIds)->get()
            ->each(fn ($t) => $t->user?->notify(new StatusTiketNotification(
                kodeTiket  : $tiket->id,
                status     : 'sedang_ditangani',
                keterangan : 'OPD melaporkan masalah belum terselesaikan dan membuka kembali tiket ini.',
                url        : route('tim_teknis.antrean'),
            )));

        return redirect()->route('opd.tiket.show', $id)
            ->with('success', 'Tiket telah dibuka kembali. Tim Teknis akan segera menangani kendala yang Anda laporkan.');
    }

    /**
     * Tampilkan form edit tiket (hanya saat status perlu_revisi).
     */
    public function edit(string $id)
    {
        $opd   = Auth::user()->opd;
        $tiket = Tiket::where('opd_id', $opd->id)
                      ->with('latestStatus')
                      ->findOrFail($id);

        if ($tiket->latestStatus?->status_tiket !== 'perlu_revisi') {
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

        if ($tiket->latestStatus?->status_tiket !== 'perlu_revisi') {
            return redirect()->route('opd.tiket.show', $id)
                ->with('error', 'Tiket hanya dapat diedit saat berstatus Perlu Revisi.');
        }

        $request->validate([
            'subjek_masalah' => 'required|string|max:255',
            'detail_masalah' => 'required|string',
            'foto_bukti'     => 'nullable|array|max:5',
            'foto_bukti.*'   => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // Hapus semua foto lama jika ada foto baru yang diunggah
        $fotoPaths = $tiket->foto_bukti ?? [];
        if ($request->hasFile('foto_bukti')) {
            foreach ($fotoPaths as $lama) {
                Storage::disk('public')->delete($lama);
            }
            $fotoPaths = [];
            foreach ($request->file('foto_bukti') as $foto) {
                $fotoPaths[] = $foto->store('tiket/foto', 'public');
            }
        }

        $tiket->update([
            'subjek_masalah'        => $request->input('subjek_masalah'),
            'detail_masalah'        => $request->input('detail_masalah'),
            'spesifikasi_perangkat' => $request->input('spesifikasi_perangkat'),
            'lokasi'                => $request->input('lokasi'),
            'foto_bukti'            => $fotoPaths ?: null,
        ]);

        // Kembalikan status ke verifikasi_admin setelah revisi
        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'catatan'      => 'Tiket telah direvisi oleh OPD dan dikembalikan untuk verifikasi ulang.',
            'created_at'   => now(),
        ]);

        return redirect()->route('opd.tiket.show', $id)
            ->with('success', 'Tiket berhasil diperbarui dan dikirim kembali untuk verifikasi.');
    }
}
