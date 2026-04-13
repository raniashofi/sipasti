<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\Bidang;
use App\Models\Opd;
use App\Models\RiwayatTransferTiket;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TimTeknis;
use App\Models\TiketTeknisi;
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
     * tiket harus punya kb_id → kb.kategori_id → kategori.bidang_id == admin.bidang_id
     */
    private function bisaTerima(Tiket $tiket, AdminHelpdesk $admin): bool
    {
        if (! $tiket->kb_id || ! $admin->bidang_id) {
            return false;
        }

        // Traversal otoritatif: KB → kategori → bidang
        $kb = $tiket->relationLoaded('kb') ? $tiket->kb : $tiket->load('kb.kategori')->kb;

        if (! $kb || ! $kb->kategori_id) {
            return false;
        }

        $kbKategori = $kb->relationLoaded('kategori') ? $kb->kategori : $kb->load('kategori')->kategori;

        if (! $kbKategori || ! $kbKategori->bidang_id) {
            return false;
        }

        return $kbKategori->bidang_id === $admin->bidang_id;
    }

    // ─── Menunggu Verifikasi ───────────────────────────────────────────────
    public function menungguVerif(Request $request)
    {
        $admin = $this->adminProfile();

        $query = Tiket::with(['opd', 'kategori', 'kb.kategori', 'latestStatus'])
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin'));

        // Tampilkan hanya tiket yang:
        // 1. Memiliki KB (kb_id NOT NULL)
        // 2. Bidang dari kategori KB sesuai dengan bidang admin
        if ($admin && $admin->bidang_id) {
            $query->whereNotNull('kb_id')
                  ->whereHas('kb.kategori', fn($q2) => $q2->where('bidang_id', $admin->bidang_id));
        }

        if ($request->filled('opd_id')) {
            $query->where('opd_id', $request->opd_id);
        }
        if ($request->filled('prioritas')) {
            $query->where('prioritas', $request->prioritas);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('id', 'like', "%$search%")
                ->orWhere('subjek_masalah', 'like', "%$search%"));
        }

        $tikets = $query->latest()->get()->map(function ($tiket) use ($admin) {
            $tiket->can_terima = $admin ? $this->bisaTerima($tiket, $admin) : false;
            return $tiket;
        });

        $opds     = Opd::orderBy('nama_opd')->get();
        $bidangs  = Bidang::all();
        $teknisis = TimTeknis::with('bidang')->where('status_teknisi', 'tersedia')->get();

        return view('admin_helpdesk.manajemen-tiket.menunggu-verif', compact(
            'tikets', 'opds', 'bidangs', 'teknisis', 'admin'
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
            'id'          => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'    => $tiket->id,
            'status_tiket'=> 'panduan_remote',
            'catatan'     => 'Tiket diterima dan diproses oleh admin helpdesk. OPD akan dihubungi via panduan remote (chat).',
            'created_at'  => now(),
        ]);

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

        $this->logAktivitas('reject', "Meminta revisi tiket #{$tiket->id} — {$request->alasan_revisi}", 'tiket', $tiket->id);

        return back()->with('success', "Permintaan revisi untuk tiket #{$tiket->id} berhasil dikirim.");
    }

    // ─── Transfer ke Admin Helpdesk Bidang Lain ───────────────────────────
    public function transfer(Request $request, string $id)
    {
        $request->validate(['bidang_id' => 'required|string|exists:bidang,id']);

        $admin = $this->adminProfile();
        $tiket = Tiket::findOrFail($id);

        RiwayatTransferTiket::create([
            'tiket_id'          => $tiket->id,
            'pengirim_admin_id' => $admin?->id,
            'penerima_admin_id' => null,
            'penerima_bidang_id'=> $request->bidang_id,
            'alasan_transfer'   => $request->instruksi ?? null,
            'waktu_transfer'    => now(),
        ]);

        // Lepas kepemilikan admin agar bisa diklaim bidang tujuan
        $tiket->update(['admin_id' => null]);

        $this->logAktivitas('transfer', "Transfer tiket #{$tiket->id} ke bidang {$request->bidang_id}", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil ditransfer ke bidang tujuan.");
    }

    // ─── Eskalasi ke Tim Teknis → perbaikan_teknis ───────────────────────
    public function eskalasi(Request $request, string $id)
    {
        $request->validate([
            'teknisi_utama_id'     => 'required|string|exists:tim_teknis,id',
            'teknisi_pendamping_id'=> 'nullable|string|exists:tim_teknis,id',
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
            'peran_teknisi'    => 'utama',
            'waktu_ditugaskan' => now(),
            'status_tugas'     => 'ditugaskan',
        ]);

        if ($request->filled('teknisi_pendamping_id')) {
            TiketTeknisi::create([
                'tiket_id'         => $tiket->id,
                'teknis_id'        => $request->teknisi_pendamping_id,
                'peran_teknisi'    => 'pendamping',
                'waktu_ditugaskan' => now(),
                'status_tugas'     => 'ditugaskan',
            ]);
        }

        $this->logAktivitas('escalate', "Eskalasi tiket #{$tiket->id} ke tim teknis", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil dieskalasi ke Tim Teknis.");
    }

    // ─── Panduan Remote ───────────────────────────────────────────────────
    public function panduan(Request $request)
    {
        $admin = $this->adminProfile();

        $query = Tiket::with(['opd', 'kb.kategori', 'kategori', 'latestStatus', 'chatRooms', 'teknisiUtama.timTeknis'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'panduan_remote'));

        if ($request->filled('opd_id'))   $query->where('opd_id', $request->opd_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->get();
        $opds     = Opd::orderBy('nama_opd')->get();
        $teknisis = TimTeknis::with('bidang')->where('status_teknisi', 'tersedia')->get();

        return view('admin_helpdesk.manajemen-tiket.panduan-remote', compact('tikets','opds','teknisis','admin'));
    }

    // ─── Distribusi & Eskalasi ────────────────────────────────────────────
    public function distribusi(Request $request)
    {
        $admin = $this->adminProfile();

        $query = Tiket::with(['opd', 'kb.kategori', 'kategori', 'latestStatus', 'teknisiUtama.timTeknis'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'perbaikan_teknis'));

        if ($request->filled('opd_id'))    $query->where('opd_id', $request->opd_id);
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->kategori_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->get();
        $opds     = Opd::orderBy('nama_opd')->get();
        $kategori = \App\Models\Kategori::orderBy('nama_kategori')->get();

        return view('admin_helpdesk.manajemen-tiket.distribusi', compact('tikets','opds','kategori','admin'));
    }

    // ─── Riwayat Tiket ────────────────────────────────────────────────────
    public function riwayat(Request $request)
    {
        $admin = $this->adminProfile();

        $statusSelesai = ['selesai','rusak_berat','dibuka_kembali'];

        $query = Tiket::with(['opd', 'kb.kategori', 'kategori', 'latestStatus', 'teknisiUtama.timTeknis'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->whereIn('status_tiket', $statusSelesai));

        if ($request->filled('opd_id'))    $query->where('opd_id', $request->opd_id);
        if ($request->filled('prioritas')) $query->where('prioritas', $request->prioritas);
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->kategori_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->get();
        $opds     = Opd::orderBy('nama_opd')->get();
        $kategori = \App\Models\Kategori::orderBy('nama_kategori')->get();

        return view('admin_helpdesk.manajemen-tiket.riwayat', compact('tikets','opds','kategori','admin'));
    }

    // ─── Export CSV ───────────────────────────────────────────────────────
    public function exportCsv(Request $request)
    {
        $tikets = Tiket::with(['opd', 'kategori', 'latestStatus'])
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin'))
            ->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="menunggu-verif-' . now()->format('Ymd-His') . '.csv"',
        ];

        $callback = function () use ($tikets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID Tiket', 'OPD', 'Subjek Masalah', 'Kategori', 'Prioritas', 'Waktu Masuk']);
            foreach ($tikets as $t) {
                fputcsv($handle, [
                    $t->id,
                    $t->opd?->nama_opd,
                    $t->subjek_masalah,
                    $t->kategori?->nama_kategori ?? '—',
                    ucfirst($t->prioritas),
                    $t->created_at?->format('d M Y H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
