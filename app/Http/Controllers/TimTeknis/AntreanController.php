<?php

namespace App\Http\Controllers\TimTeknis;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\ChatRoom;
use App\Models\Opd;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TiketTeknisi;
use App\Models\TimTeknis;
use App\Notifications\StatusTiketNotification;
use App\Notifications\TiketMasukNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AntreanController extends Controller
{
    private function teknisProfile(): ?TimTeknis
    {
        return TimTeknis::with('bidang')->where('user_id', Auth::id())->first();
    }

    private function logAktivitas(string $jenis, string $detail, string $namaTable, string $idRecord): void
    {
        ActivityLog::create([
            'user_id'         => Auth::id(),
            'role_pelaku'     => 'tim_teknis',
            'jenis_aktivitas' => $jenis,
            'detail_tindakan' => $detail,
            'ip_address'      => request()->ip(),
            'waktu_eksekusi'  => now(),
            'nama_tabel'      => $namaTable,
            'id_record'       => $idRecord,
        ]);
    }

    // ─── Antrean Tugas ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $teknis = $this->teknisProfile();

        $query = Tiket::with([
            'opd', 'kategori', 'kb.kategori', 'sopInternal', 'latestStatus', 'chatRooms',
            'statusTiket', 'solutionNode',
            // Load both aktif and selesai records so dibuka_kembali tickets (where
            // status_tugas may still be 'selesai' from old data) still resolve the peran.
            'tiketTeknisi' => fn($q) => $q
                ->with('timTeknis')
                ->where('teknis_id', $teknis?->id)
                ->whereIn('status_tugas', ['aktif', 'selesai']),
        ])
            ->where(fn($q) => $q
                // Branch 1 — normal on-progress: teknisi must have an aktif assignment
                ->where(fn($q2) => $q2
                    ->whereHas('latestStatus', fn($q3) => $q3->where('status_tiket', 'perbaikan_teknis'))
                    ->whereHas('tiketTeknisi', fn($q3) => $q3
                        ->where('teknis_id', $teknis?->id)
                        ->where('status_tugas', 'aktif')
                        // ✅ Filter by bidang: hanya tiket dari bidang teknisi ini
                        ->whereHas('timTeknis', fn($q4) => $q4->where('bidang_id', $teknis?->bidang_id)))
                )
                // Branch 2 — re-opened: teknisi just needs to be assigned (status_tugas
                // may still be 'selesai' if old data predates the bukaKembali() update)
                ->orWhere(fn($q2) => $q2
                    ->whereHas('latestStatus', fn($q3) => $q3->where('status_tiket', 'dibuka_kembali'))
                    ->whereHas('tiketTeknisi', fn($q3) => $q3
                        ->where('teknis_id', $teknis?->id)
                        // ✅ Filter by bidang: hanya tiket dari bidang teknisi ini
                        ->whereHas('timTeknis', fn($q4) => $q4->where('bidang_id', $teknis?->bidang_id)))
                )
            );

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id', 'like', "%$s%")
                ->orWhere('subjek_masalah', 'like', "%$s%"));
        }

        $rawTikets = $query->latest()->get();

        // Hitung unread messages per tiket dalam satu query
        $userId = Auth::id();
        $teknisChatRoomMap = $rawTikets
            ->flatMap(fn($t) => $t->chatRooms->where('nama_roomchat', 'teknis'))
            ->keyBy('tiket_id');
        $roomIds = $teknisChatRoomMap->pluck('id');

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

        $batasHari = $teknis?->bidang?->batas_hari_pengerjaan;

        $allTikets = $rawTikets->map(function ($tiket) use ($teknisChatRoomMap, $unreadMap, $batasHari) {
            $tiketTeknisiUtama = $tiket->tiketTeknisi->first();
            $tiket->my_peran = $tiketTeknisiUtama?->peran_teknisi ?? 'teknisi_pendamping';

            // Cek Keterlambatan (SLA)
            $tiket->is_telat = false;
            $tiket->hari_telat = 0;
            if ($batasHari && $tiketTeknisiUtama && $tiketTeknisiUtama->waktu_ditugaskan) {
                $deadline = \Carbon\Carbon::parse($tiketTeknisiUtama->waktu_ditugaskan)->addDays($batasHari);
                if (now()->gt($deadline)) {
                    $tiket->is_telat = true;
                    $tiket->hari_telat = (int) ceil($deadline->floatDiffInDays(now()));
                }
            }

            $bukaKembali = $tiket->statusTiket->where('status_tiket', 'dibuka_kembali')->last();
            if ($bukaKembali) {
                $tiket->alasan_buka_kembali     = $bukaKembali->catatan;
                $tiket->file_bukti_buka_kembali = $bukaKembali->file_bukti;
                $tiket->pernah_dibuka_kembali_opd = true;
            } else {
                // Tiket yang dibuka kembali melalui jalur panduan_remote (diselesaikan admin helpdesk)
                $prefix = '[Dibuka Kembali oleh OPD] ';
                $bukaKembaliRemote = $tiket->statusTiket
                    ->where('status_tiket', 'panduan_remote')
                    ->filter(fn($s) => str_starts_with($s->catatan ?? '', $prefix))
                    ->last();
                $tiket->alasan_buka_kembali     = $bukaKembaliRemote
                    ? substr($bukaKembaliRemote->catatan, strlen($prefix))
                    : null;
                $tiket->file_bukti_buka_kembali = $bukaKembaliRemote?->file_bukti;
                $tiket->pernah_dibuka_kembali_opd = $bukaKembaliRemote !== null;
            }

            $perbaikanTeknis = $tiket->statusTiket->where('status_tiket', 'perbaikan_teknis')->last();
            $tiket->catatan_admin = $perbaikanTeknis?->catatan;
            $room = $teknisChatRoomMap->get($tiket->id);
            $tiket->unread_count = $room ? (int) ($unreadMap->get($room->id, 0)) : 0;
            return $tiket;
        });

        $countAll        = $allTikets->count();
        $countUtama      = $allTikets->where('my_peran', 'teknisi_utama')->count();
        $countPendamping = $allTikets->where('my_peran', 'teknisi_pendamping')->count();

        $peran  = $request->filled('peran') && in_array($request->peran, ['teknisi_utama', 'teknisi_pendamping'])
                    ? $request->peran : null;
        $tikets = $peran ? $allTikets->filter(fn($t) => $t->my_peran === $peran)->values() : $allTikets;

        $opds = Opd::orderBy('nama_opd')->get();

        return view('tim_teknis.antrean', compact('tikets', 'opds', 'teknis', 'countAll', 'countUtama', 'countPendamping'));
    }

    // ─── Selesai ──────────────────────────────────────────────────────────
    public function selesai(Request $request, string $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        $teknis = $this->teknisProfile();

        // Self-heal: dibuka_kembali tickets whose TiketTeknisi wasn't flipped back to aktif
        $latestStatusTiket = StatusTiket::where('tiket_id', $id)->orderByDesc('created_at')->value('status_tiket');
        if ($latestStatusTiket === 'dibuka_kembali') {
            TiketTeknisi::where('tiket_id', $id)
                ->where('teknis_id', $teknis?->id)
                ->where('status_tugas', 'selesai')
                ->update(['status_tugas' => 'aktif']);
        }

        // Cari tiket yang ditugaskan ke teknisi utama (status_tugas bisa 'aktif' atau 'selesai')
        $tiket  = Tiket::whereHas('tiketTeknisi', fn($q) => $q
            ->where('teknis_id', $teknis?->id)
            ->whereIn('status_tugas', ['aktif', 'selesai'])
            ->where('peran_teknisi', 'teknisi_utama'))
            ->findOrFail($id);

        // ✅ SELALU create StatusTiket 'selesai' saat teknisi utama click selesai
        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
            'catatan'      => $request->catatan ?? 'Tiket berhasil diperbaiki oleh tim teknis.',
            'created_at'   => now(),
        ]);

        // ✅ SELALU update SEMUA teknisi (utama + pendamping) ke status_tugas = 'selesai'
        // Karena hanya teknisi utama yang punya button selesai, ketika dia click,
        // semua teknisi harus move to riwayat/completed
        TiketTeknisi::where('tiket_id', $tiket->id)
            ->where('status_tugas', 'aktif')
            ->update(['status_tugas' => 'selesai']);

        // Kasus: tiket pernah dibuka kembali → langsung tutup tanpa menunggu 7 hari
        $pernahDibukaKembali = StatusTiket::where('tiket_id', $tiket->id)
            ->where('status_tiket', 'dibuka_kembali')
            ->exists();
        if ($pernahDibukaKembali) {
            StatusTiket::create([
                'id'           => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'tiket_ditutup',
                'catatan'      => 'Tiket ditutup otomatis setelah diselesaikan kembali oleh Tim Teknis.',
                'created_at'   => now(),
            ]);
        }

        // ✅ Notifikasi ke OPD bahwa tiket selesai diperbaiki
        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'selesai',
            keterangan : 'Perangkat Anda telah berhasil diperbaiki oleh tim teknis.',
            url        : route('opd.tiket.show', $tiket->id),
        ));

        $this->logAktivitas('approve', "Tiket #{$tiket->id} selesai diperbaiki — {$tiket->subjek_masalah}", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil ditandai selesai.");
    }

    // ─── Gagal / Rusak Berat ─────────────────────────────────────────────
    public function gagal(Request $request, string $id)
    {
        $request->validate([
            'analisis_kerusakan'          => 'required|string|max:2000',
            'spesifikasi_perangkat_rusak' => 'nullable|string|max:1000',
            'rekomendasi'                 => 'required|string|max:2000',
        ]);

        $teknis = $this->teknisProfile();

        $latestStatusTiket = StatusTiket::where('tiket_id', $id)->orderByDesc('created_at')->value('status_tiket');
        if ($latestStatusTiket === 'dibuka_kembali') {
            TiketTeknisi::where('tiket_id', $id)
                ->where('teknis_id', $teknis?->id)
                ->where('status_tugas', 'selesai')
                ->update(['status_tugas' => 'aktif']);
        }

        // Cari tiket yang ditugaskan ke teknisi utama (status_tugas bisa 'aktif' atau 'selesai')
        $tiket  = Tiket::whereHas('tiketTeknisi', fn($q) => $q
            ->where('teknis_id', $teknis?->id)
            ->whereIn('status_tugas', ['aktif', 'selesai'])
            ->where('peran_teknisi', 'teknisi_utama'))
            ->findOrFail($id);

        // Cek apakah ada teknisi pendamping yang masih aktif
        $adaPendampingAktif = TiketTeknisi::where('tiket_id', $tiket->id)
            ->where('peran_teknisi', 'teknisi_pendamping')
            ->where('status_tugas', 'aktif')
            ->exists();

        // Hanya update status teknisi utama (yang sedang login)
        TiketTeknisi::where('tiket_id', $tiket->id)
            ->where('teknis_id', $teknis?->id)
            ->where('status_tugas', 'aktif')
            ->update(['status_tugas' => 'selesai']);

        // Jika tidak ada pendamping aktif, buat status tiket rusak_berat
        if (!$adaPendampingAktif) {
            StatusTiket::create([
                'id'                          => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'                    => $tiket->id,
                'status_tiket'                => 'rusak_berat',
                'catatan'                     => $request->analisis_kerusakan,
                'spesifikasi_perangkat_rusak' => $request->spesifikasi_perangkat_rusak,
                'rekomendasi'                 => $request->rekomendasi,
                'created_at'                  => now(),
            ]);
        } else {
            // Ada pendamping aktif, simpan analisis sebagai status tanpa menutup tiket
            StatusTiket::create([
                'id'           => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'perbaikan_teknis',
                'catatan'      => '[Analisis Kerusakan dari Teknisi Utama] ' . $request->analisis_kerusakan,
                'created_at'   => now(),
            ]);
        }

        // Jika tidak ada pendamping aktif dan pernah dibuka kembali → langsung tutup
        if (!$adaPendampingAktif) {
            $pernahDibukaKembali = StatusTiket::where('tiket_id', $tiket->id)
                ->where('status_tiket', 'dibuka_kembali')
                ->exists();
            if ($pernahDibukaKembali) {
                StatusTiket::create([
                    'id'           => 'STS-' . strtoupper(Str::random(10)),
                    'tiket_id'     => $tiket->id,
                    'status_tiket' => 'tiket_ditutup',
                    'catatan'      => 'Tiket ditutup otomatis setelah ditangani kembali oleh Tim Teknis.',
                    'created_at'   => now(),
                ]);
            }

            // Notifikasi ke OPD bahwa perangkat dinyatakan rusak berat
            $tiket->load('opd.user');
            $tiket->opd?->user?->notify(new StatusTiketNotification(
                kodeTiket  : $tiket->id,
                status     : 'selesai',
                keterangan : 'Perangkat Anda dinyatakan rusak berat dan tidak dapat diperbaiki. Lihat detail tiket untuk rekomendasi selanjutnya.',
                url        : route('opd.tiket.show', $tiket->id),
            ));
        }

        $this->logAktivitas('reject', "Tiket #{$tiket->id} gagal diperbaiki (rusak berat) — {$tiket->subjek_masalah}", 'tiket', $tiket->id);

        return back()->with('success', "Rekomendasi untuk tiket #{$tiket->id} berhasil dikirim.");
    }

    // ─── Riwayat Tugas ───────────────────────────────────────────────────
    public function riwayat(Request $request)
    {
        $teknis = $this->teknisProfile();

        // Exclude tickets that have been re-opened (latestStatus = dibuka_kembali);
        // those belong in antrean, not riwayat.
        $notDibukaKembali = fn($tq) => $tq->whereHas('latestStatus',
            fn($lq) => $lq->where('status_tiket', '!=', 'dibuka_kembali')
        );

        // ✅ Riwayat HANYA menampilkan tickets yang benar-benar selesai
        // (ada StatusTiket dengan status_tiket = selesai/rusak_berat/tiket_ditutup)
        $hasCompletionStatus = fn($tq) => $tq->whereHas('statusTiket',
            fn($sq) => $sq->whereIn('status_tiket', ['selesai', 'rusak_berat', 'tiket_ditutup'])
        );

        // ✅ Filter by bidang: hanya tiket dari bidang teknisi ini
        $fromTeknisBidang = fn($tq) => $tq->whereHas('tiketTeknisi',
            fn($ttq) => $ttq->whereHas('timTeknis', fn($ttq2) => $ttq2->where('bidang_id', $teknis?->bidang_id))
        );

        $query = TiketTeknisi::with(['tiket.opd', 'tiket.kategori', 'tiket.kb.kategori', 'tiket.latestStatus', 'tiket.solutionNode'])
            ->where('teknis_id', $teknis?->id)
            ->where('status_tugas', 'selesai')
            ->whereHas('tiket', $notDibukaKembali)
            ->whereHas('tiket', $hasCompletionStatus)
            ->whereHas('tiket', $fromTeknisBidang);

        $peran = $request->filled('peran') && in_array($request->peran, ['teknisi_utama', 'teknisi_pendamping'])
            ? $request->peran : null;

        if ($peran) {
            $query->where('peran_teknisi', $peran);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('tiket', fn($q) => $q
                ->where('id', 'like', "%$s%")
                ->orWhere('subjek_masalah', 'like', "%$s%"));
        }

        $batasHari = $teknis->bidang?->batas_hari_pengerjaan;

        $riwayats = $query->latest('waktu_ditugaskan')->get()->map(function ($row) use ($batasHari) {
            $row->is_telat = false;
            $row->hari_telat = 0;

            if ($batasHari && $row->waktu_ditugaskan) {
                // Cari status selesai/rusak_berat terakhir dari tiket tersebut
                $statusSelesai = $row->tiket->statusTiket
                    ->whereIn('status_tiket', ['selesai', 'rusak_berat', 'tiket_ditutup'])
                    ->sortByDesc('created_at')
                    ->first();

                if ($statusSelesai && $statusSelesai->created_at) {
                    $deadline = \Carbon\Carbon::parse($row->waktu_ditugaskan)->addDays($batasHari);
                    if (\Carbon\Carbon::parse($statusSelesai->created_at)->gt($deadline)) {
                        $row->is_telat = true;
                        $row->hari_telat = (int) ceil($deadline->floatDiffInDays(\Carbon\Carbon::parse($statusSelesai->created_at)));
                    }
                }
            }
            return $row;
        });

        $countAll        = TiketTeknisi::where('teknis_id', $teknis?->id)->where('status_tugas', 'selesai')->whereHas('tiket', $notDibukaKembali)->whereHas('tiket', $hasCompletionStatus)->whereHas('tiket', $fromTeknisBidang)->count();
        $countUtama      = TiketTeknisi::where('teknis_id', $teknis?->id)->where('status_tugas', 'selesai')->where('peran_teknisi', 'teknisi_utama')->whereHas('tiket', $notDibukaKembali)->whereHas('tiket', $hasCompletionStatus)->whereHas('tiket', $fromTeknisBidang)->count();
        $countPendamping = TiketTeknisi::where('teknis_id', $teknis?->id)->where('status_tugas', 'selesai')->where('peran_teknisi', 'teknisi_pendamping')->whereHas('tiket', $notDibukaKembali)->whereHas('tiket', $hasCompletionStatus)->whereHas('tiket', $fromTeknisBidang)->count();

        return view('tim_teknis.riwayat', compact('riwayats', 'teknis', 'countAll', 'countUtama', 'countPendamping'));
    }

    // ─── Kembalikan ke Admin Helpdesk ─────────────────────────────────────
    public function kembalikan(Request $request, string $id)
    {
        $request->validate([
            'alasan_kembalikan' => 'required|string|max:1000',
        ]);

        $teknis = $this->teknisProfile();

        $latestStatusTiket = StatusTiket::where('tiket_id', $id)->orderByDesc('created_at')->value('status_tiket');
        if ($latestStatusTiket === 'dibuka_kembali') {
            TiketTeknisi::where('tiket_id', $id)
                ->where('teknis_id', $teknis?->id)
                ->where('status_tugas', 'selesai')
                ->update(['status_tugas' => 'aktif']);
        }

        // Cari tiket yang ditugaskan ke teknisi utama (status_tugas bisa 'aktif' atau 'selesai')
        $tiket  = Tiket::whereHas('tiketTeknisi', fn($q) => $q
            ->where('teknis_id', $teknis?->id)
            ->whereIn('status_tugas', ['aktif', 'selesai'])
            ->where('peran_teknisi', 'teknisi_utama'))
            ->findOrFail($id);

        // Cek apakah ada teknisi pendamping yang masih aktif
        $adaPendampingAktif = TiketTeknisi::where('tiket_id', $tiket->id)
            ->where('peran_teknisi', 'teknisi_pendamping')
            ->where('status_tugas', 'aktif')
            ->exists();

        // Hanya update status teknisi utama (yang sedang login)
        TiketTeknisi::where('tiket_id', $tiket->id)
            ->where('teknis_id', $teknis?->id)
            ->where('status_tugas', 'aktif')
            ->update(['status_tugas' => 'selesai']);

        // Jika tidak ada pendamping aktif, kembalikan ke verifikasi admin
        if (!$adaPendampingAktif) {
            StatusTiket::create([
                'id'           => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'verifikasi_admin',
                'catatan'      => '[Dikembalikan oleh Tim Teknis] ' . $request->alasan_kembalikan,
                'created_at'   => now(),
            ]);
        } else {
            // Ada pendamping aktif, buat status untuk tracking tapi tidak ubah status_tiket
            StatusTiket::create([
                'id'           => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'perbaikan_teknis',
                'catatan'      => '[Dikembalikan oleh Teknisi Utama ke Admin] ' . $request->alasan_kembalikan,
                'created_at'   => now(),
            ]);
        }

        // Notifikasi ke Admin Helpdesk pemilik tiket (hanya jika tidak ada pendamping aktif)
        if (!$adaPendampingAktif) {
            $tiket->load('admin.user');
            if ($tiket->admin?->user) {
                $tiket->admin->user->notify(new TiketMasukNotification(
                    kodeTiket : $tiket->id,
                    namaOpd   : 'Tim Teknis',
                    url       : route('admin_helpdesk.tiket.menunggu'),
                ));
            } else {
                // Admin belum assign → kirim ke semua admin bidang yang sesuai
                $adminQuery = AdminHelpdesk::with('user');
                if ($tiket->bidang_id) {
                    $adminQuery->where('bidang_id', $tiket->bidang_id);
                }
                $adminQuery->get()->each(fn ($a) => $a->user?->notify(new TiketMasukNotification(
                    kodeTiket : $tiket->id,
                    namaOpd   : 'Tim Teknis',
                    url       : route('admin_helpdesk.tiket.menunggu'),
                )));
            }
        }

        $this->logAktivitas('escalate', "Tiket #{$tiket->id} dikembalikan ke admin helpdesk — {$request->alasan_kembalikan}", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil dikembalikan ke Admin Helpdesk.");
    }
}
