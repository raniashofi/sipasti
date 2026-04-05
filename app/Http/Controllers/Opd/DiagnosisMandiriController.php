<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\KnowledgeBase;
use App\Models\NodeDiagnosis;
use Illuminate\Http\Request;

class DiagnosisMandiriController extends Controller
{
    /**
     * Step 1 — Pilih kategori masalah.
     */
    public function index()
    {
        $kategori = Kategori::whereHas('knowledgeBase', fn($q) =>
            $q->where('status_publikasi', 'published')
              ->where('visibilitas_akses', 'opd')
        )->get();

        return view('opd.buat-pengaduan.index', compact('kategori'));
    }

    /**
     * Step 2 — Cari root node (pertanyaan pertama) dari KB kategori ini,
     * lalu redirect ke showNode.
     */
    public function mulai(string $kategoriId)
    {
        $kategori = Kategori::findOrFail($kategoriId);

        $kbIds = KnowledgeBase::where('kategori_id', $kategoriId)
            ->where('status_publikasi', 'published')
            ->where('visibilitas_akses', 'opd')
            ->pluck('id');

        if ($kbIds->isEmpty()) {
            return redirect()->route('opd.tiket.create')
                ->with('info', 'Tidak ada panduan diagnosis untuk kategori ini. Silakan buat tiket langsung.');
        }

        // Node yang BUKAN root = node yang dirujuk sebagai id_next_ya atau id_next_tidak
        $referencedIds = NodeDiagnosis::whereIn('kb_id', $kbIds)
            ->get(['id_next_ya', 'id_next_tidak'])
            ->flatMap(fn($n) => [$n->id_next_ya, $n->id_next_tidak])
            ->filter()
            ->unique()
            ->toArray();

        $rootNode = NodeDiagnosis::whereIn('kb_id', $kbIds)
            ->where('tipe_node', 'pertanyaan')
            ->whereNotIn('id', $referencedIds)
            ->first();

        if (!$rootNode) {
            return redirect()->route('opd.tiket.create');
        }

        return redirect()->to(
            route('opd.diagnosis.node', $rootNode->id) . '?' . http_build_query([
                'kategori_id'   => $kategoriId,
                'kategori_nama' => $kategori->nama_kategori,
                'diagnosa'      => '',
                'q'             => 1,
            ])
        );
    }

    /**
     * Step 2+ — Tampilkan node (pertanyaan atau solusi).
     */
    public function showNode(string $nodeId, Request $request)
    {
        $node = NodeDiagnosis::findOrFail($nodeId);

        $kategoriId   = $request->query('kategori_id', '');
        $kategoriNama = $request->query('kategori_nama', '');
        $diagnosa     = $request->query('diagnosa', '');
        $qNum         = (int) $request->query('q', 1);

        if ($node->tipe_node === 'solusi') {
            $kb = $node->kb_id ? KnowledgeBase::find($node->kb_id) : null;
            return view('opd.buat-pengaduan.solusi', compact(
                'node', 'kb', 'kategoriId', 'kategoriNama', 'diagnosa'
            ));
        }

        // Siapkan URL jawaban Ya dan Tidak untuk dipakai di view (Alpine.js navigates to these)
        $baseQuery = ['kategori_id' => $kategoriId, 'kategori_nama' => $kategoriNama, 'q' => $qNum + 1];

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
            'node', 'kategoriId', 'kategoriNama', 'diagnosa', 'qNum', 'urlYa', 'urlTidak'
        ));
    }

    /**
     * Step 3 — Tampilkan formulir pengaduan (setelah solusi tidak berhasil).
     */
    public function showTiket(Request $request)
    {
        $kategoriId   = $request->query('kategori_id', '');
        $kategoriNama = $request->query('kategori_nama', '');
        $diagnosa     = $request->query('diagnosa', '');

        return view('opd.buat-pengaduan.tiket', compact('kategoriId', 'kategoriNama', 'diagnosa'));
    }
}
