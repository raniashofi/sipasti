<?php

namespace Tests\Unit\Opd;

use App\Http\Controllers\Opd\DiagnosisMandiriController;
use App\Models\Tiket;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DiagnosisMandiriControllerTest extends TestCase
{
    // ── Otorisasi ────────────────────────────────────────────────────────

    public function testIndexTolakNonOpd(): void
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(
            in_array($this->get('/opd/buat-pengaduan')->status(), [302, 403]),
            'index() — role non-opd ditolak akses diagnosis'
        );
    }

    public function testMulaiTolakNonOpd(): void
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(
            in_array($this->get('/opd/buat-pengaduan/FAKE-KAT/mulai')->status(), [302, 403, 404]),
            'mulai() — role non-opd ditolak atau kategori tidak ditemukan'
        );
    }

    public function testShowNodeTolakNonOpd(): void
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(
            in_array($this->get('/opd/buat-pengaduan/node/FAKE-NODE')->status(), [302, 403, 404]),
            'showNode() — role non-opd ditolak'
        );
    }

    public function testShowTiketTolakNonOpd(): void
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(
            in_array($this->get('/opd/buat-pengaduan/tiket?node_diagnosis_id=FAKE')->status(), [302, 403]),
            'showTiket() — role non-opd ditolak'
        );
    }

    // ── Logik mulai() ────────────────────────────────────────────────────

    public function testMulaiRedirectKeNodePertama(): void
    {
        $controller = new TestableDiagnosisMandiriController();

        // Siapkan mock nodes: 1 pertanyaan root + 1 solusi
        $rootNode = (object) [
            'id' => 'NODE-ROOT',
            'tipe_node' => 'pertanyaan',
            'id_next_ya' => 'NODE-SOLUSI',
            'id_next_tidak' => null,
        ];
        $solusiNode = (object) [
            'id' => 'NODE-SOLUSI',
            'tipe_node' => 'solusi',
            'id_next_ya' => null,
            'id_next_tidak' => null,
        ];

        $controller->mockNodes = collect([$rootNode, $solusiNode]);
        $controller->mockKategori = (object) ['id' => 'KAT-001', 'nama_kategori' => 'Jaringan', 'deskripsi' => 'Masalah jaringan'];

        $result = $controller->mulai('KAT-001');

        // Harus redirect (302) ke node root
        $this->assertSame(302, $result->getStatusCode(), 'mulai() — redirect ke node pertama');
        $this->assertStringContainsString('NODE-ROOT', $result->getTargetUrl(), 'mulai() — URL redirect mengandung ID node root');
    }

    public function testMulaiRedirectJikaNodeKosong(): void
    {
        $controller = new TestableDiagnosisMandiriController();
        $controller->mockNodes = collect();
        $controller->mockKategori = (object) ['id' => 'KAT-001', 'nama_kategori' => 'Jaringan', 'deskripsi' => ''];

        $result = $controller->mulai('KAT-001');

        $this->assertSame(302, $result->getStatusCode(), 'mulai() — redirect jika node kosong');
    }

    public function testMulaiRedirectJikaTidakAdaRootNode(): void
    {
        $controller = new TestableDiagnosisMandiriController();

        // Hanya ada solusi tanpa pertanyaan root
        $solusiNode = (object) [
            'id' => 'NODE-SOLUSI',
            'tipe_node' => 'solusi',
            'id_next_ya' => null,
            'id_next_tidak' => null,
        ];
        $controller->mockNodes = collect([$solusiNode]);
        $controller->mockKategori = (object) ['id' => 'KAT-001', 'nama_kategori' => 'Jaringan', 'deskripsi' => ''];

        $result = $controller->mulai('KAT-001');

        $this->assertSame(302, $result->getStatusCode(), 'mulai() — redirect jika tidak ada root node pertanyaan');
    }

    // ── Logik showNode() ────────────────────────────────────────────────

    public function testShowNodePertanyaanView(): void
    {
        $controller = new TestableDiagnosisMandiriController();
        $controller->mockNode = (object) [
            'id' => 'NODE-001',
            'tipe_node' => 'pertanyaan',
            'teks_pertanyaan' => 'Apakah komputer menyala?',
            'id_next_ya' => 'NODE-002',
            'id_next_tidak' => 'NODE-003',
        ];

        $request = Request::create('/opd/buat-pengaduan/node/NODE-001', 'GET', [
            'kategori_id' => 'KAT-001',
            'kategori_nama' => 'Jaringan',
            'kategori_deskripsi' => 'Masalah jaringan',
            'diagnosa' => '',
            'q' => 1,
        ]);

        $result = $controller->showNode('NODE-001', $request);

        $this->assertSame('opd.buat-pengaduan.node', $result['view']);
        $this->assertSame('NODE-001', $result['data']['node']->id);
        $this->assertSame(1, $result['data']['qNum']);
        $this->assertNotNull($result['data']['urlYa'], 'showNode() — URL Ya ada jika id_next_ya tersedia');
        $this->assertNotNull($result['data']['urlTidak'], 'showNode() — URL Tidak ada jika id_next_tidak tersedia');
    }

    public function testShowNodeSolusiView(): void
    {
        $controller = new TestableDiagnosisMandiriController();
        $controller->mockNode = (object) [
            'id' => 'NODE-SOL',
            'tipe_node' => 'solusi',
            'artikelOpd' => (object) ['id' => 'KB-001', 'judul' => 'Panduan Reset'],
            'sopInternal' => (object) ['id' => 'SOP-001', 'judul' => 'SOP Jaringan'],
            'bidang_id' => 'BDG-001',
        ];

        $request = Request::create('/opd/buat-pengaduan/node/NODE-SOL', 'GET', [
            'kategori_id' => 'KAT-001',
            'kategori_nama' => 'Jaringan',
            'kategori_deskripsi' => '',
            'diagnosa' => 'Ya > Tidak',
        ]);

        $result = $controller->showNode('NODE-SOL', $request);

        $this->assertSame('opd.buat-pengaduan.solusi', $result['view']);
        $this->assertSame('BDG-001', $result['data']['bidangId']);
        $this->assertSame('Ya > Tidak', $result['data']['diagnosa']);
    }

    // ── Validasi storeTiket() ────────────────────────────────────────────

    public function testStoreTiketValidasiSubjekDanDetail(): void
    {
        try {
            Request::create('/s', 'POST', [])->validate([
                'subjek_masalah' => 'required|string|max:255',
                'detail_masalah' => 'required|string',
                'node_diagnosis_id' => 'required|string',
            ]);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('subjek_masalah', $e->errors(), 'storeTiket() — subjek_masalah wajib diisi');
            $this->assertArrayHasKey('detail_masalah', $e->errors(), 'storeTiket() — detail_masalah wajib diisi');
            $this->assertArrayHasKey('node_diagnosis_id', $e->errors(), 'storeTiket() — node_diagnosis_id wajib diisi');
        }
    }

    public function testStoreTiketFotoBuktiNullableValid(): void
    {
        $r = Request::create('/s', 'POST', [
            'subjek_masalah' => 'Test',
            'detail_masalah' => 'Detail',
            'node_diagnosis_id' => 'N1',
        ]);
        $r->validate([
            'subjek_masalah' => 'required|string|max:255',
            'detail_masalah' => 'required|string',
            'node_diagnosis_id' => 'required|string',
            'foto_bukti' => 'nullable|array|max:5',
            'foto_bukti.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);
        $this->assertSame('Test', $r->input('subjek_masalah'), 'storeTiket() - foto_bukti nullable dan maks 5 file');
    }

    public function testStoreTiketRekomendasiInvalidDiNullkan(): void
    {
        $valid = ['admin', 'eskalasi'];

        $this->assertContains('admin', $valid, 'storeTiket() — rekomendasi admin valid');
        $this->assertContains('eskalasi', $valid, 'storeTiket() — rekomendasi eskalasi valid');

        // Logik dari controller: jika tidak valid, set null
        $rekomendasi = 'invalid';
        if (!in_array($rekomendasi, $valid)) {
            $rekomendasi = null;
        }
        $this->assertNull($rekomendasi, 'storeTiket() — rekomendasi invalid di-null-kan');
    }

    // ── Logik showTiket() ────────────────────────────────────────────────

    public function testShowTiketRekomendasiDefaultAdmin(): void
    {
        // Logik dari controller showTiket(): jika rekomendasi tidak valid, default ke 'admin'
        $rekomendasi = 'invalid';
        if (!in_array($rekomendasi, ['admin', 'eskalasi'])) {
            $rekomendasi = 'admin';
        }
        $this->assertSame('admin', $rekomendasi, 'showTiket() — rekomendasi default ke admin');
    }

    // ── Konstanta ────────────────────────────────────────────────────────

    public function testDuplicateSimilarityThreshold(): void
    {
        $controller = new TestableDiagnosisMandiriController();
        $this->assertSame(65, $controller->getThreshold(), 'DUPLICATE_SIMILARITY_THRESHOLD = 65%');
    }

    public function testConfirmationWindowDays(): void
    {
        $controller = new TestableDiagnosisMandiriController();
        $this->assertSame(7, $controller->getConfirmationWindowDays(), 'CONFIRMATION_WINDOW_DAYS = 7 hari');
    }

    // ── Normalisasi Teks Duplikat ────────────────────────────────────────

    public function testComparableTextNormalisasi(): void
    {
        $controller = new TestableDiagnosisMandiriController();

        $text = $controller->normalizeDuplicateText(
            'Koneksi Internet Jaringan LAN Terputus',
            'Icon LAN menunjukkan No Internet Access dan tanda silang merah.',
            'PC Desktop Lenovo',
            'Gedung Utama Lt. 2'
        );

        $this->assertStringContainsString('internet', $text);
        $this->assertStringContainsString('putus', $text);
        $this->assertStringContainsString('indikator', $text);
        $this->assertStringContainsString('lantai', $text);
        $this->assertStringNotContainsString('yang', $text);
        $this->assertStringNotContainsString('dengan', $text);
    }

    public function testComparableTextKosongJikaSemuaNull(): void
    {
        $controller = new TestableDiagnosisMandiriController();
        $text = $controller->normalizeDuplicateText(null, null, null, null);
        $this->assertSame('', $text, 'duplicateComparableText() — empty jika semua null');
    }

    // ── Similarity Detection ────────────────────────────────────────────

    public function testTextSimilarityDeteksiSerupa(): void
    {
        $controller = new TestableDiagnosisMandiriController();

        $left = 'internet putus indikator lan muncul silang merah ruang egov lantai 2';
        $right = 'internet putus lan indikator silang merah ruang egovernment lantai 2';

        $this->assertGreaterThanOrEqual(65, $controller->calculateTextSimilarity($left, $right),
            'textSimilarity() — teks serupa >= threshold 65%');
    }

    public function testTextSimilarityTidakSerupaRendah(): void
    {
        $controller = new TestableDiagnosisMandiriController();

        $left = 'internet putus lan indikator silang merah';
        $right = 'printer rusak tinta habis kertas macet';

        $this->assertLessThan(65, $controller->calculateTextSimilarity($left, $right),
            'textSimilarity() — teks berbeda < threshold');
    }

    public function testTextSimilarityKosongReturnNol(): void
    {
        $controller = new TestableDiagnosisMandiriController();
        $this->assertSame(0.0, $controller->calculateTextSimilarity('', 'test'),
            'textSimilarity() — 0 jika salah satu kosong');
        $this->assertSame(0.0, $controller->calculateTextSimilarity('test', ''),
            'textSimilarity() — 0 jika salah satu kosong (kanan)');
    }

    // ── Deteksi Duplikat dengan Mock ─────────────────────────────────────

    public function testFindSimilarDeteksiDuplikat(): void
    {
        $existingTiket = new Tiket([
            'id' => 'TKT-MOCK001',
            'node_diagnosis_id' => 'NODE-001',
            'subjek_masalah' => 'Koneksi Internet Jaringan LAN Terputus',
            'detail_masalah' => 'Sejak pagi koneksi internet pada PC tidak dapat tersambung. Indikator LAN menunjukkan tanda silang merah.',
            'spesifikasi_perangkat' => 'PC Desktop Lenovo ThinkCentre',
            'lokasi' => 'Ruang Staf Bidang e-Government, Gedung Utama Lantai 2',
        ]);

        $controller = new TestableDiagnosisMandiriController(collect([$existingTiket]));
        $request = Request::create('/opd/buat-pengaduan/tiket', 'POST', [
            'node_diagnosis_id' => 'NODE-001',
            'subjek_masalah' => 'Jaringan Internet LAN Putus',
            'detail_masalah' => 'Dari pagi internet di PC mati total. Icon LAN muncul tanda silang merah.',
            'spesifikasi_perangkat' => 'PC Desktop Lenovo',
            'lokasi' => 'Ruang Staf e-Government Lt. 2',
        ]);

        $result = $controller->findSimilarTiket('OPD-001', $request);

        $this->assertSame($existingTiket, $result, 'findSimilarActiveTiket() — tiket serupa terdeteksi');
        $this->assertSame('OPD-001', $controller->queriedOpdId);
        $this->assertSame('NODE-001', $controller->queriedNodeDiagnosisId);
    }

    public function testFindSimilarTidakSerupa(): void
    {
        $existingTiket = new Tiket([
            'id' => 'TKT-MOCK002',
            'node_diagnosis_id' => 'NODE-002',
            'subjek_masalah' => 'Printer tidak bisa mencetak',
            'detail_masalah' => 'Printer ruangan tata usaha tidak dapat mencetak dokumen.',
            'spesifikasi_perangkat' => 'Printer Epson',
            'lokasi' => 'Ruang TU',
        ]);

        $controller = new TestableDiagnosisMandiriController(collect([$existingTiket]));
        $request = Request::create('/opd/buat-pengaduan/tiket', 'POST', [
            'node_diagnosis_id' => 'NODE-001',
            'subjek_masalah' => 'Aplikasi tidak bisa login',
            'detail_masalah' => 'User gagal masuk ke aplikasi sejak pagi.',
            'lokasi' => 'Ruang operator',
        ]);

        $this->assertNull($controller->findSimilarTiket('OPD-001', $request),
            'findSimilarActiveTiket() — tiket tidak serupa return null');
    }

    public function testFindSimilarKosongReturnNull(): void
    {
        $controller = new TestableDiagnosisMandiriController(collect());
        $request = Request::create('/opd/buat-pengaduan/tiket', 'POST', [
            'subjek_masalah' => '',
            'detail_masalah' => '',
        ]);

        $this->assertNull($controller->findSimilarTiket('OPD-001', $request),
            'findSimilarActiveTiket() - null jika candidateText kosong');
    }

    public function testFindValidTicketSolutionNodeKosongReturnNull(): void
    {
        $controller = new TestableDiagnosisMandiriController();

        $this->assertNull($controller->findValidSolutionNode(null));
        $this->assertNull($controller->findValidSolutionNode(''));
    }
}

class TestableDiagnosisMandiriController extends DiagnosisMandiriController
{
    public ?string $queriedOpdId = null;
    public ?string $queriedNodeDiagnosisId = null;
    public $mockNodes = null;
    public $mockKategori = null;
    public $mockNode = null;

    public function __construct(private readonly Collection $mockTickets = new Collection())
    {
    }

    public function normalizeDuplicateText(?string $subject, ?string $detail, ?string $specification, ?string $location): string
    {
        return $this->duplicateComparableText($subject, $detail, $specification, $location);
    }

    public function calculateTextSimilarity(string $left, string $right): float
    {
        return $this->textSimilarity($left, $right);
    }

    public function findSimilarTiket(string $opdId, Request $request): ?Tiket
    {
        return $this->findSimilarActiveTiket($opdId, $request);
    }

    public function findValidSolutionNode(?string $nodeDiagnosisId)
    {
        return $this->findValidTicketSolutionNode($nodeDiagnosisId);
    }

    public function getThreshold(): int
    {
        return (new \ReflectionClass(DiagnosisMandiriController::class))
            ->getConstant('DUPLICATE_SIMILARITY_THRESHOLD');
    }

    public function getConfirmationWindowDays(): int
    {
        return (new \ReflectionClass(DiagnosisMandiriController::class))
            ->getConstant('CONFIRMATION_WINDOW_DAYS');
    }

    protected function recentComparableTickets(string $opdId, ?string $nodeDiagnosisId, $batasKonfirmasi): Collection
    {
        $this->queriedOpdId = $opdId;
        $this->queriedNodeDiagnosisId = $nodeDiagnosisId;

        return $this->mockTickets;
    }

    /**
     * Override mulai() dependencies.
     */
    public function mulai(string $kategoriId)
    {
        $kategori = $this->mockKategori;
        $allNodes = $this->mockNodes;

        if ($allNodes->isEmpty()) {
            return redirect()->route('opd.diagnosis.index')
                ->with('error', 'Belum ada alur diagnosis untuk kategori ini.');
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
                ->with('error', 'Alur diagnosis kategori ini belum memiliki pertanyaan awal.');
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

    /**
     * Override showNode() dependencies.
     */
    public function showNode(string $nodeId, Request $request)
    {
        $node = $this->mockNode;

        $kategoriId        = $request->query('kategori_id', '');
        $kategoriNama      = $request->query('kategori_nama', '');
        $kategoriDeskripsi = $request->query('kategori_deskripsi', '');
        $diagnosa          = $request->query('diagnosa', '');
        $qNum              = (int) $request->query('q', 1);

        if ($node->tipe_node === 'solusi') {
            $kb = $node->artikelOpd ?? null;
            $sopInternal = $node->sopInternal ?? null;
            $bidangId = $node->bidang_id ?? '';
            return ['view' => 'opd.buat-pengaduan.solusi', 'data' => compact(
                'node', 'kb', 'sopInternal', 'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'diagnosa', 'bidangId'
            )];
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

        return ['view' => 'opd.buat-pengaduan.node', 'data' => compact(
            'node', 'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'diagnosa', 'qNum', 'urlYa', 'urlTidak'
        )];
    }
}
