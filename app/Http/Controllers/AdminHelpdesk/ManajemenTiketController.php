<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\Bidang;
use App\Models\Opd;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TimTeknis;
use App\Models\TiketTeknisi;
use App\Notifications\StatusTiketNotification;
use App\Notifications\TugasBaruNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ManajemenTiketController extends Controller
{
    private function adminProfile()
    {
        return AdminHelpdesk::with('bidang')->where('user_id', Auth::id())->first();
    }

    private function logAktivitas(string $jenis, string $detail, string $namaTable, string $idRecord): void
    {
        ActivityLog::create([
            'user_id'         => Auth::id(),
            'role_pelaku'     => 'admin_helpdesk',
            'jenis_aktivitas' => $jenis,
            'detail_tindakan' => $detail,
            'ip_address'      => request()->ip(),
            'waktu_eksekusi'  => now(),
            'nama_tabel'      => $namaTable,
            'id_record'       => $idRecord,
        ]);
    }

    /**
     * Cek apakah admin bisa menerima tiket:
     * tiket harus punya kategori_id → kategori_sistem.bidang_id == admin.bidang_id
     */
    private function bisaTerima(Tiket $tiket, AdminHelpdesk $admin): bool
    {
        if (! $admin->bidang_id) {
            return false;
        }

        if ($tiket->bidang_id) {
            return $tiket->bidang_id === $admin->bidang_id;
        }

        return false;
    }

    // ─── Menunggu Verifikasi ───────────────────────────────────────────────
    public function menungguVerif(Request $request)
    {
        $admin          = $this->adminProfile();
        $prefixKembali  = '[Dikembalikan oleh Tim Teknis] ';
        $prefixTransfer = '[Transfer ke ';

        $applyFilters = function ($q) use ($request) {
            if ($request->filled('opd_id')) $q->where('opd_id', $request->opd_id);
            if ($request->filled('rekomendasi_penanganan')) {
                $q->where('rekomendasi_penanganan', $request->rekomendasi_penanganan);
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $q->where(fn($q2) => $q2->where('id', 'like', "%$s%")->orWhere('subjek_masalah', 'like', "%$s%"));
            }
            return $q;
        };

        // 1. Tiket baru dari OPD: admin_id null, bidang sesuai, bukan tiket transfer
        $queryBaru = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'solutionNode'])
            ->whereNull('admin_id')
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where(fn($q2) => $q2->whereNull('catatan')->orWhere('catatan', 'not like', '[Transfer ke %]')));
        if ($admin && $admin->bidang_id) {
            $queryBaru->whereHas('kategori', fn($q) => $q->where('bidang_id', $admin->bidang_id));
        }
        $applyFilters($queryBaru);

        // 2. Tiket ditransfer masuk ke bidang admin ini
        $queryTransfer = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'solutionNode'])
            ->whereNull('admin_id')
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where('catatan', 'like', $prefixTransfer . ($admin?->bidang_id ?? '') . ']%'));
        $applyFilters($queryTransfer);

        // 3. Tiket dikembalikan teknisi: masih milik admin ini
        $queryKembali = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where('catatan', 'like', $prefixKembali . '%'));
        $applyFilters($queryKembali);

        $mapTiket = function ($tiket) use ($admin, $prefixKembali) {
            $tiket->can_terima        = $admin ? $this->bisaTerima($tiket, $admin) : false;
            $catatan                  = $tiket->latestStatus?->catatan ?? '';
            $tiket->dikembalikan      = str_starts_with($catatan, $prefixKembali);
            $tiket->alasan_kembalikan = $tiket->dikembalikan ? substr($catatan, strlen($prefixKembali)) : null;
            return $tiket;
        };

        $tiketsVerif = $queryBaru->latest()->get()
            ->merge($queryTransfer->latest()->get())
            ->map($mapTiket)
            ->sortByDesc(fn($t) => $t->rekomendasi_penanganan === 'eskalasi' ? 1 : 0)
            ->values();

        $tiketsDikembalikan = $queryKembali->latest()->get()
            ->map($mapTiket)
            ->values();

        $opds     = Opd::orderBy('nama_opd')->get();
        $bidangs  = Bidang::all();
        $teknisis = TimTeknis::with('bidang')
            ->withCount(['tiketTeknisi as tiket_aktif_count' => fn($q) => $q->where('status_tugas', 'aktif')])
            ->where('status_teknisi', 'online')
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin_helpdesk.manajemen-tiket.menunggu-verif', compact(
            'tiketsVerif', 'tiketsDikembalikan', 'opds', 'bidangs', 'teknisis', 'admin'
        ));
    }

    // ─── Terima & Proses Tiket → panduan_remote ───────────────────────────
    public function terimaProses(Request $request, string $id)
    {
        $admin = $this->adminProfile();
        $tiket = Tiket::with(['kategori', 'kb'])->findOrFail($id);

        // Validasi: tiket harus punya KB + kategori + bidang yang sama dengan admin
        if (! $this->bisaTerima($tiket, $admin)) {
            return back()->with('error', 'Anda tidak dapat menerima tiket ini. Tiket harus memiliki KB, kategori, dan bidang yang sesuai dengan bidang Anda.');
        }

        $tiket->update(['admin_id' => $admin->id]);

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'panduan_remote',
            'catatan'     => 'Tiket diterima dan diproses oleh admin helpdesk. OPD akan dihubungi via panduan remote (chat).',
            'created_at'  => now(),
        ]);

        // Notifikasi ke OPD bahwa tiket sudah diverifikasi
        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'panduan_remote',
            keterangan : 'Tiket Anda telah diverifikasi dan admin helpdesk siap memberikan panduan remote.',
            url        : route('opd.tiket.show', $tiket->id),
        ));

        $this->logAktivitas('approve', "Menerima tiket #{$tiket->id} — {$tiket->subjek_masalah}", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil diterima dan masuk ke Panduan Remote.");
    }

    // ─── Minta Revisi ─────────────────────────────────────────────────────
    public function revisi(Request $request, string $id)
    {
        $request->validate(['alasan_revisi' => 'required|string|max:1000']);

        $tiket = Tiket::findOrFail($id);

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'perlu_revisi',
            'catatan'      => $request->alasan_revisi,
            'created_at'   => now(),
        ]);

        // Notifikasi ke OPD bahwa tiket perlu direvisi
        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'diverifikasi',
            keterangan : 'Tiket Anda perlu direvisi: ' . $request->alasan_revisi,
            url        : route('opd.tiket.edit', $tiket->id),
        ));

        $this->logAktivitas('reject', "Meminta revisi tiket #{$tiket->id} — {$request->alasan_revisi}", 'tiket', $tiket->id);

        return back()->with('success', "Permintaan revisi untuk tiket #{$tiket->id} berhasil dikirim.");
    }

    // ─── Transfer ke Admin Helpdesk Bidang Lain ───────────────────────────
    public function transfer(Request $request, string $id)
    {
        $request->validate(['bidang_id' => 'required|string|exists:bidang,id']);

        $admin = $this->adminProfile();
        $tiket = Tiket::findOrFail($id);

        $instruksi = $request->instruksi ?? 'Dialihkan oleh admin helpdesk.';

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'catatan'      => '[Transfer ke ' . $request->bidang_id . '] ' . $instruksi,
            'created_at'   => now(),
        ]);

        // Lepas kepemilikan agar tiket muncul di menunggu verif admin bidang tujuan
        $tiket->update(['admin_id' => null]);

        $this->logAktivitas('update', "Transfer tiket #{$tiket->id} ke bidang {$request->bidang_id} — {$instruksi}", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil ditransfer ke bidang tujuan.");
    }

    // ─── Eskalasi ke Tim Teknis → perbaikan_teknis ───────────────────────
    public function eskalasi(Request $request, string $id)
    {
        $request->validate([
            'teknisi_utama_id'         => 'required|string|exists:tim_teknis,id',
            'teknisi_pendamping_ids'   => 'nullable|array',
            'teknisi_pendamping_ids.*' => 'string|exists:tim_teknis,id',
        ]);

        $admin = $this->adminProfile();
        $tiket = Tiket::findOrFail($id);

        $tiket->update(['admin_id' => $admin?->id]);

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'perbaikan_teknis',
            'catatan'      => $request->instruksi ?? 'Tiket dieskalasi ke tim teknis untuk penanganan langsung.',
            'created_at'   => now(),
        ]);

        TiketTeknisi::create([
            'tiket_id'         => $tiket->id,
            'teknis_id'        => $request->teknisi_utama_id,
            'peran_teknisi'    => 'teknisi_utama',
            'waktu_ditugaskan' => now(),
            'status_tugas'     => 'aktif',
        ]);

        $pendampingIds = [];
        if ($request->filled('teknisi_pendamping_ids') && is_array($request->teknisi_pendamping_ids)) {
            foreach (array_unique($request->teknisi_pendamping_ids) as $pendampingId) {
                if ($pendampingId !== $request->teknisi_utama_id) {
                    TiketTeknisi::create([
                        'tiket_id'         => $tiket->id,
                        'teknis_id'        => $pendampingId,
                        'peran_teknisi'    => 'teknisi_pendamping',
                        'waktu_ditugaskan' => now(),
                        'status_tugas'     => 'aktif',
                    ]);
                    $pendampingIds[] = $pendampingId;
                }
            }
        }

        // Notifikasi ke Tim Teknis yang ditugaskan
        $judulMasalah = $tiket->subjek_masalah;
        $urlAntrean   = route('tim_teknis.antrean');
        $allTeknisiIds = array_merge([$request->teknisi_utama_id], $pendampingIds);
        TimTeknis::with('user')->whereIn('id', $allTeknisiIds)->get()
            ->each(fn ($t) => $t->user?->notify(new TugasBaruNotification(
                kodeTiket    : $tiket->id,
                judulMasalah : $judulMasalah,
                url          : $urlAntrean,
            )));

        $this->logAktivitas('escalate', "Eskalasi tiket #{$tiket->id} ke tim teknis", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil dieskalasi ke Tim Teknis.");
    }

    // ─── Selesaikan Tiket dari Panduan Remote ────────────────────────────
    public function selesaiOlehAdmin(Request $request, string $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:1000']);

        $admin = $this->adminProfile();
        $tiket = Tiket::where('admin_id', $admin?->id)
                      ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'panduan_remote'))
                      ->findOrFail($id);

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
            'catatan'      => '[Diselesaikan oleh Admin Helpdesk] ' . ($request->catatan ?? 'Tiket berhasil diselesaikan melalui panduan remote.'),
            'created_at'   => now(),
        ]);

        // Kasus 2: tiket pernah dibuka kembali lewat jalur panduan remote → langsung tutup
        $pernahDibukaKembaliRemote = StatusTiket::where('tiket_id', $tiket->id)
            ->where('status_tiket', 'panduan_remote')
            ->where('catatan', 'like', '[Dibuka Kembali oleh OPD]%')
            ->exists();
        if ($pernahDibukaKembaliRemote) {
            StatusTiket::create([
                'id'           => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'tiket_ditutup',
                'catatan'      => 'Tiket ditutup otomatis setelah diselesaikan kembali oleh Admin Helpdesk.',
                'created_at'   => now(),
            ]);
        }

        // Notifikasi ke OPD bahwa tiket selesai
        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'selesai',
            keterangan : 'Tiket Anda telah diselesaikan melalui panduan remote oleh admin helpdesk.',
            url        : route('opd.tiket.show', $tiket->id),
        ));

        $this->logAktivitas('approve', "Tiket #{$tiket->id} diselesaikan oleh admin helpdesk — {$tiket->subjek_masalah}", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil ditandai selesai.");
    }

    // ─── Panduan Remote ───────────────────────────────────────────────────
    public function panduan(Request $request)
    {
        $admin = $this->adminProfile();

        $query = Tiket::with(['opd', 'kategori.bidang', 'latestStatus', 'sopInternal', 'chatRooms', 'teknisiUtama.timTeknis', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'panduan_remote'));

        if ($request->filled('opd_id'))   $query->where('opd_id', $request->opd_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->paginate(10);
        $opds     = Opd::orderBy('nama_opd')->get();
        $teknisis = TimTeknis::with('bidang')->where('status_teknisi', 'online')->orderBy('nama_lengkap')->get();

        return view('admin_helpdesk.manajemen-tiket.panduan-remote', compact('tikets','opds','teknisis','admin'));
    }

    // ─── Distribusi & Eskalasi ────────────────────────────────────────────
    public function distribusi(Request $request)
    {
        $admin = $this->adminProfile();

        $query = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'statusTiket', 'teknisiUtama.timTeknis', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->whereIn('status_tiket', ['perbaikan_teknis', 'dibuka_kembali']));

        if ($request->filled('opd_id'))    $query->where('opd_id', $request->opd_id);
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->kategori_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->paginate(10);
        $opds     = Opd::orderBy('nama_opd')->get();
        $kategori = \App\Models\KategoriSistem::orderBy('nama_kategori')->get();

        return view('admin_helpdesk.manajemen-tiket.distribusi', compact('tikets','opds','kategori','admin'));
    }

    // ─── Riwayat Tiket ────────────────────────────────────────────────────
    public function riwayat(Request $request)
    {
        $admin = $this->adminProfile();

        $statusSelesai = ['selesai', 'rusak_berat', 'tiket_ditutup'];

        $query = Tiket::with(['opd', 'kategori', 'latestStatus', 'teknisiUtama.timTeknis', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->whereIn('status_tiket', $statusSelesai));

        if ($request->filled('opd_id'))    $query->where('opd_id', $request->opd_id);
        if ($request->filled('rekomendasi_penanganan')) {
            $query->where('rekomendasi_penanganan', $request->rekomendasi_penanganan);
        }
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->kategori_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->get();
        $opds     = Opd::orderBy('nama_opd')->get();
        $kategori = \App\Models\KategoriSistem::orderBy('nama_kategori')->get();

        return view('admin_helpdesk.manajemen-tiket.riwayat', compact('tikets','opds','kategori','admin'));
    }

    // ─── Export CSV ───────────────────────────────────────────────────────
    public function exportCsv(Request $request)
    {
        $tikets = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'solutionNode'])
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin'))
            ->latest()
            ->paginate(10);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="menunggu-verif-' . now()->format('Ymd-His') . '.csv"',
        ];

        $callback = function () use ($tikets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID Tiket', 'OPD', 'Subjek Masalah', 'Kategori', 'Rekomendasi Penanganan', 'Waktu Masuk']);
            foreach ($tikets as $t) {
                $rekomendasiLabel = match($t->rekomendasi_penanganan) {
                    'eskalasi' => 'Perlu Dieskalasi ke Tim Teknis',
                    default    => 'Dapat Ditangani Admin',
                };
                fputcsv($handle, [
                    $t->id,
                    $t->opd?->nama_opd,
                    $t->subjek_masalah,
                    $t->kategori?->nama_kategori ?? '—',
                    $rekomendasiLabel,
                    $t->created_at?->format('d M Y H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
