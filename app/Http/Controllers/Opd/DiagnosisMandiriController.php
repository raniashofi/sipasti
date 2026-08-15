<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\AdminHelpdesk;
use App\Models\KategoriSistem;
use App\Models\NodeDiagnosis;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Notifications\TiketMasukNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DiagnosisMandiriController extends Controller
{
    private const DUPLICATE_SIMILARITY_THRESHOLD = 65;
    private const CONFIRMATION_WINDOW_DAYS = 7;

    public function index()
    {
        $kategori = KategoriSistem::whereHas('nodes')
            ->whereDoesntHave('nodes', function ($q) {
                $q->where('tipe_node', 'solusi')
                  ->where(function ($q2) {
                      $q2->whereNull('bidang_id')
                         ->orWhereNull('artikel_opd_id')
                         ->orWhereNull('sop_internal_id');
                  });
            })
            ->get();

        return view('opd.buat-pengaduan.index', compact('kategori'));
    }

    public function mulai(string $kategoriId)
    {
        $kategori = KategoriSistem::findOrFail($kategoriId);

        $allNodes = NodeDiagnosis::where('kategori_id', $kategoriId)->get();

        if ($allNodes->isEmpty()) {
            return redirect()->route('opd.diagnosis.index')
                ->with('error', 'Belum ada alur diagnosis untuk kategori ini. Tiket hanya dapat dibuat setelah memilih solusi diagnosis.');
        }

        $referencedIds = $allNodes
            ->flatMap(fn($n) => [$n->id_next_ya, $n->id_next_tidak])
            ->filter()
            ->unique()
            ->toArray();

        $rootNode = $allNodes
            ->where('tipe_node', 'pertanyaan')
            ->whereNotIn('id', $referencedIds)
            ->first();

        if (!$rootNode) {
            return redirect()->route('opd.diagnosis.index')
                ->with('error', 'Alur diagnosis kategori ini belum memiliki pertanyaan awal. Tiket belum dapat dibuat.');
        }

        return redirect()->to(
            route('opd.diagnosis.node', $rootNode->id) . '?' . http_build_query([
                'kategori_id'        => $kategoriId,
                'kategori_nama'      => $kategori->nama_kategori,
                'kategori_deskripsi' => $kategori->deskripsi ?? '',
                'diagnosa'           => '',
                'q'                  => 1,
            ])
        );
    }

    public function showNode(string $nodeId, Request $request)
    {
        $node = NodeDiagnosis::findOrFail($nodeId);

        $kategoriId        = $request->query('kategori_id', '');
        $kategoriNama      = $request->query('kategori_nama', '');
        $kategoriDeskripsi = $request->query('kategori_deskripsi', '');
        $diagnosa          = $request->query('diagnosa', '');
        $qNum              = (int) $request->query('q', 1);

        if ($node->tipe_node === 'solusi') {
            $kb = $node->artikelOpd;
            $sopInternal = $node->sopInternal;
            $bidangId = $node->bidang_id ?? '';
            return view('opd.buat-pengaduan.solusi', compact(
                'node', 'kb', 'sopInternal', 'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'diagnosa', 'bidangId'
            ));
        }

        $baseQuery = [
            'kategori_id'        => $kategoriId,
            'kategori_nama'      => $kategoriNama,
            'kategori_deskripsi' => $kategoriDeskripsi,
            'q'                  => $qNum + 1,
        ];

        $urlYa = $node->id_next_ya
            ? route('opd.diagnosis.node', $node->id_next_ya) . '?' . http_build_query(
                array_merge($baseQuery, ['diagnosa' => trim($diagnosa . ' > Ya')])
              )
            : null;

        $urlTidak = $node->id_next_tidak
            ? route('opd.diagnosis.node', $node->id_next_tidak) . '?' . http_build_query(
                array_merge($baseQuery, ['diagnosa' => trim($diagnosa . ' > Tidak')])
              )
            : null;

        return view('opd.buat-pengaduan.node', compact(
            'node', 'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'diagnosa', 'qNum', 'urlYa', 'urlTidak'
        ));
    }

    /**
     * AJAX endpoint: cek apakah ada tiket serupa yang masih aktif.
     * Dipanggil via fetch sebelum form benar-benar di-submit,
     * supaya halaman tidak reload dan foto-foto di Alpine.js tetap aman.
     */
    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'subjek_masalah'    => 'required|string|max:255',
            'detail_masalah'    => 'required|string',
            'node_diagnosis_id' => 'required|string',
        ]);

        $opd = Auth::user()->opd;
        if (!$opd) {
            return response()->json(['duplicate' => false]);
        }

        $duplicateTiket = $this->findSimilarActiveTiket($opd->id, $request);

        if ($duplicateTiket) {
            return response()->json([
                'duplicate' => true,
                'id'        => $duplicateTiket->id,
                'subjek'    => $duplicateTiket->subjek_masalah,
                'url'       => route('opd.tiket.show', $duplicateTiket->id),
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    public function storeTiket(Request $request)
    {
        $request->validate([
            'subjek_masalah'  => 'required|string|max:255',
            'detail_masalah'  => 'required|string',
            'node_diagnosis_id' => [
                'required',
                Rule::exists('node_diagnosis', 'id')->where(function ($query) {
                    $query->where('tipe_node', 'solusi')
                        ->whereNotNull('bidang_id')
                        ->whereNotNull('artikel_opd_id')
                        ->whereNotNull('sop_internal_id');
                }),
            ],
            'foto_bukti'      => 'nullable|array|max:5',
            'foto_bukti.*'    => 'image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'node_diagnosis_id.required' => 'Tiket hanya dapat dibuat setelah Anda memilih solusi diagnosis.',
            'node_diagnosis_id.exists'   => 'Solusi diagnosis tidak valid atau belum lengkap.',
            'foto_bukti.*.max'           => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
        ]);

        $opd = Auth::user()->opd;
        if (!$opd) {
            abort(403, 'Data OPD tidak ditemukan.');
        }

        $fotoPaths = [];

        if ($request->hasFile('foto_bukti')) {
            foreach ($request->file('foto_bukti') as $foto) {
                $fotoPaths[] = $foto->store('tiket/foto', 'public');
            }
        }

        $rekomendasi = $request->input('rekomendasi_penanganan');
        if (!in_array($rekomendasi, ['admin', 'eskalasi'])) {
            $rekomendasi = null;
        }

        $tiket = Tiket::create([
            'opd_id'                  => $opd->id,
            'node_diagnosis_id'       => $request->input('node_diagnosis_id'),
            'rekomendasi_penanganan'  => $rekomendasi,
            'subjek_masalah'          => $request->input('subjek_masalah'),
            'detail_masalah'          => $request->input('detail_masalah'),
            'spesifikasi_perangkat'   => $request->input('spesifikasi_perangkat'),
            'lokasi'                  => $request->input('lokasi'),
        ]);

        // Simpan foto ke tabel tiket_bukti_foto
        foreach ($fotoPaths as $fotoPath) {
            \App\Models\TiketBuktiFoto::create([
                'tiket_id'  => $tiket->id,
                'foto_path' => $fotoPath,
            ]);
        }

        StatusTiket::create([
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'created_at'   => now(),
        ]);

        // Notifikasi ke Admin Helpdesk sesuai bidang dari node solusi
        $adminQuery = AdminHelpdesk::with('user');
        $bidangId = NodeDiagnosis::find($request->input('node_diagnosis_id'))?->bidang_id;
        if ($bidangId) {
            $adminQuery->where('bidang_id', $bidangId);
        }
        $namaOpd = $opd->nama_opd ?? 'OPD';
        $url     = route('admin_helpdesk.tiket.menunggu');
        $adminQuery->get()->each(function ($admin) use ($tiket, $namaOpd, $url) {
            $admin->user?->notify(new TiketMasukNotification($tiket->id, $namaOpd, $url));
        });

        return redirect()->route('opd.tiket.index')
                         ->with('success', 'Tiket #' . $tiket->id . ' berhasil dikirim! Admin Helpdesk akan memverifikasi pengaduan Anda.');
    }

    public function showTiket(Request $request)
    {
        $kategoriId        = $request->query('kategori_id', '');
        $kategoriNama      = $request->query('kategori_nama', '');
        $kategoriDeskripsi = $request->query('kategori_deskripsi', '');
        $nodeDiagnosisId   = $request->query('node_diagnosis_id', '');
        $rekomendasi       = $request->query('rekomendasi_penanganan', 'admin');

        $solutionNode = $this->findValidTicketSolutionNode($nodeDiagnosisId);
        if (!$solutionNode) {
            return redirect()->route('opd.diagnosis.index')
                ->with('error', 'Silakan selesaikan diagnosis dan pilih solusi sebelum membuat tiket.');
        }

        if (!in_array($rekomendasi, ['admin', 'eskalasi'])) {
            $rekomendasi = 'admin';
        }

        return view('opd.buat-pengaduan.tiket', compact(
            'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'nodeDiagnosisId', 'rekomendasi'
        ));
    }

    protected function findValidTicketSolutionNode(?string $nodeDiagnosisId): ?NodeDiagnosis
    {
        if (!$nodeDiagnosisId) {
            return null;
        }

        return NodeDiagnosis::where('id', $nodeDiagnosisId)
            ->where('tipe_node', 'solusi')
            ->whereNotNull('bidang_id')
            ->whereNotNull('artikel_opd_id')
            ->whereNotNull('sop_internal_id')
            ->first();
    }

    protected function findSimilarActiveTiket(string $opdId, Request $request): ?Tiket
    {
        $candidateText = $this->duplicateComparableText(
            $request->input('subjek_masalah', ''),
            $request->input('detail_masalah', ''),
            $request->input('spesifikasi_perangkat', ''),
            $request->input('lokasi', '')
        );

        if ($candidateText === '') {
            return null;
        }

        $nodeDiagnosisId = $request->input('node_diagnosis_id') ?: null;
        $batasKonfirmasi = now()->subDays(self::CONFIRMATION_WINDOW_DAYS);

        return $this->recentComparableTickets($opdId, $nodeDiagnosisId, $batasKonfirmasi)
            ->first(function (Tiket $tiket) use ($candidateText) {
                $existingText = $this->duplicateComparableText(
                    $tiket->subjek_masalah,
                    $tiket->detail_masalah,
                    $tiket->spesifikasi_perangkat ?? '',
                    $tiket->lokasi ?? ''
                );

                return $this->textSimilarity($candidateText, $existingText) >= self::DUPLICATE_SIMILARITY_THRESHOLD;
            });
    }

    protected function recentComparableTickets(string $opdId, ?string $nodeDiagnosisId, $batasKonfirmasi)
    {
        $query = Tiket::where('opd_id', $opdId)
            ->with('latestStatus')
            ->whereHas('latestStatus', function ($q) use ($batasKonfirmasi) {
                $q->where('status_tiket', '!=', 'tiket_ditutup')
                    ->where(function ($q2) use ($batasKonfirmasi) {
                        $q2->whereNotIn('status_tiket', ['selesai', 'rusak_berat'])
                           ->orWhere('created_at', '>', $batasKonfirmasi);
                    });
            })
            ->latest()
            ->limit(30);

        if ($nodeDiagnosisId) {
            $query->where('node_diagnosis_id', $nodeDiagnosisId);
        }

        return $query->get();
    }

    protected function duplicateComparableText(?string $subject, ?string $detail, ?string $specification, ?string $location): string
    {
        $text = strtolower(trim(
            ($subject ?? '') . ' ' .
            ($detail ?? '') . ' ' .
            ($specification ?? '') . ' ' .
            ($location ?? '')
        ));

        $phraseMap = [
            'no internet access' => 'internet putus',
            'no internet'        => 'internet putus',
            'mati total'         => 'putus',
            'tanda silang merah' => 'silang merah',
            'sudut kanan bawah'  => 'layar bawah',
            'layar bawah'        => 'layar bawah',
            'lantai'             => 'lantai',
            'lt.'                => 'lantai',
            'lt '                => 'lantai ',
        ];

        $text = str_replace(array_keys($phraseMap), array_values($phraseMap), $text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text) ?? '';

        $stopWords = [
            'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'pada', 'dengan',
            'saya', 'kami', 'ini', 'itu', 'ada', 'tidak', 'bisa', 'dapat', 'sudah',
            'belum', 'karena', 'agar', 'mohon', 'tolong', 'sejak', 'pagi', 'tadi',
            'wib', 'pukul', 'jam', 'nya', 'juga', 'sangat', 'segera',
        ];

        $wordMap = [
            'jaringan'      => 'internet',
            'koneksi'       => 'internet',
            'terputus'      => 'putus',
            'tersambung'    => 'sambung',
            'terhubung'     => 'sambung',
            'connect'       => 'sambung',
            'connected'     => 'sambung',
            'colok'         => 'pasang',
            'pasang'        => 'pasang',
            'cabut'         => 'cabut',
            'ditarik'       => 'cabut',
            'dicabut'       => 'cabut',
            'dipasang'      => 'pasang',
            'icon'          => 'indikator',
            'ikon'          => 'indikator',
            'menunjukkan'   => 'muncul',
            'muncul'        => 'muncul',
            'perubahan'     => 'ubah',
            'diperbaiki'    => 'tangani',
            'ditangani'     => 'tangani',
            'menghambat'    => 'ganggu',
            'mengganggu'    => 'ganggu',
            'rekapitulasi'  => 'rekap',
            'rekap'         => 'rekap',
            'bidang'        => 'bidang',
            'government'    => 'egovernment',
            'e'             => 'egovernment',
            'lt'            => 'lantai',
            'no'            => 'nomor',
        ];

        return collect(preg_split('/\s+/', $text) ?: [])
            ->map(fn($word) => $wordMap[$word] ?? $word)
            ->filter(fn($word) => (strlen($word) > 2 || is_numeric($word)) && !in_array($word, $stopWords, true))
            ->values()
            ->implode(' ');
    }

    protected function textSimilarity(string $left, string $right): float
    {
        if ($left === '' || $right === '') {
            return 0;
        }

        similar_text($left, $right, $characterSimilarity);

        $leftTokens = array_values(array_unique(explode(' ', $left)));
        $rightTokens = array_values(array_unique(explode(' ', $right)));
        $intersection = count(array_intersect($leftTokens, $rightTokens));
        $union = max(count(array_unique(array_merge($leftTokens, $rightTokens))), 1);
        $jaccardSimilarity = ($intersection / $union) * 100;
        $diceSimilarity = (2 * $intersection / max(count($leftTokens) + count($rightTokens), 1)) * 100;
        $containmentSimilarity = ($intersection / max(min(count($leftTokens), count($rightTokens)), 1)) * 100;

        return max($characterSimilarity, $jaccardSimilarity, $diceSimilarity, $containmentSimilarity);
    }
}
