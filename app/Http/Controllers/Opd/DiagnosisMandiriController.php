<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\KnowledgeBase;
use App\Models\NodeDiagnosis;
use App\Models\StatusTiket;
use App\Models\Tiket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiagnosisMandiriController extends Controller
{
    /**
     * Step 1 — Pilih kategori masalah.
     */
    public function index()
    {
        $kategori = Kategori::with('bidang')->get();

        return view('opd.buat-pengaduan.index', compact('kategori'));
    }

    /**
     * Step 2 — Cari root node (pertanyaan pertama) dari kategori ini,
     * lalu redirect ke showNode.
     */
    public function mulai(string $kategoriId)
    {
        $kategori = Kategori::findOrFail($kategoriId);

        // Semua node milik kategori ini
        $allNodes = NodeDiagnosis::where('kategori_id', $kategoriId)->get();

        if ($allNodes->isEmpty()) {
            return redirect()->route('opd.diagnosis.tiket', [
                'kategori_id'        => $kategoriId,
                'kategori_nama'      => $kategori->nama_kategori,
                'kategori_deskripsi' => $kategori->deskripsi ?? '',
                'diagnosa'           => '',
            ])->with('info', 'Belum ada alur diagnosis untuk kategori ini. Silakan buat tiket langsung.');
        }

        // Node yang BUKAN root = node yang dirujuk sebagai id_next_ya atau id_next_tidak
        $referencedIds = $allNodes
            ->flatMap(fn($n) => [$n->id_next_ya, $n->id_next_tidak])
            ->filter()
            ->unique()
            ->toArray();

        // Root node = node pertanyaan yang tidak dirujuk oleh node manapun
        $rootNode = $allNodes
            ->where('tipe_node', 'pertanyaan')
            ->whereNotIn('id', $referencedIds)
            ->first();

        if (!$rootNode) {
            return redirect()->route('opd.diagnosis.tiket', [
                'kategori_id'        => $kategoriId,
                'kategori_nama'      => $kategori->nama_kategori,
                'kategori_deskripsi' => $kategori->deskripsi ?? '',
                'diagnosa'           => '',
            ]);
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
     * Step 2+ — Tampilkan node (pertanyaan atau solusi).
     */
    public function showNode(string $nodeId, Request $request)
    {
        $node = NodeDiagnosis::findOrFail($nodeId);

        $kategoriId        = $request->query('kategori_id', '');
        $kategoriNama      = $request->query('kategori_nama', '');
        $kategoriDeskripsi = $request->query('kategori_deskripsi', '');
        $diagnosa          = $request->query('diagnosa', '');
        $qNum              = (int) $request->query('q', 1);

        if ($node->tipe_node === 'solusi') {
            $kb = $node->kb_id ? KnowledgeBase::find($node->kb_id) : null;
            return view('opd.buat-pengaduan.solusi', compact(
                'node', 'kb', 'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'diagnosa'
            ));
        }

        // Siapkan URL jawaban Ya dan Tidak untuk dipakai di view (Alpine.js navigates to these)
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
     * Step 3b — Simpan tiket ke database.
     */
    public function storeTiket(Request $request)
    {
        $request->validate([
            'subjek_masalah' => 'required|string|max:255',
            'detail_masalah' => 'required|string',
            'foto_bukti'     => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $opd = Auth::user()->opd;
        if (!$opd) {
            abort(403, 'Data OPD tidak ditemukan.');
        }

        // Generate ID: TKT-{10 char random}
        $tiketId = 'TKT-' . strtoupper(Str::random(10));

        // Handle file upload
        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')
                                ->store('tiket/foto', 'public');
        }

        $tiket = Tiket::create([
            'id'                    => $tiketId,
            'opd_id'                => $opd->id,
            'kb_id'                 => $request->input('kb_id') ?: null,
            'kategori_id'           => $request->input('kategori_id') ?: null,
            'subjek_masalah'        => $request->input('subjek_masalah'),
            'detail_masalah'        => $request->input('detail_masalah'),
            'spesifikasi_perangkat' => $request->input('spesifikasi_perangkat'),
            'lokasi'                => $request->input('lokasi'),
            'foto_bukti'            => $fotoPath,
        ]);

        // Set status awal: verifikasi_admin
        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);

        return redirect()->route('opd.tiket.index')
                         ->with('success', 'Tiket #' . $tiketId . ' berhasil dikirim! Admin Helpdesk akan memverifikasi pengaduan Anda.');
    }

    /**
     * Step 3 — Tampilkan formulir pengaduan (setelah solusi tidak berhasil).
     */
    public function showTiket(Request $request)
    {
        $kategoriId        = $request->query('kategori_id', '');
        $kategoriNama      = $request->query('kategori_nama', '');
        $kategoriDeskripsi = $request->query('kategori_deskripsi', '');
        $kbId              = $request->query('kb_id', '');

        return view('opd.buat-pengaduan.tiket', compact(
            'kategoriId', 'kategoriNama', 'kategoriDeskripsi', 'kbId'
        ));
    }
}
