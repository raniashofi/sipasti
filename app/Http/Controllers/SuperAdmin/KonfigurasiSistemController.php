<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\KategoriSistem;
use App\Models\KnowledgeBase;
use App\Models\NodeDiagnosis;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KonfigurasiSistemController extends Controller
{
    private array $bidangLabel = [
        'e_government'                     => 'E-Government',
        'infrastruktur_teknologi_informasi' => 'Infrastruktur TI',
        'statistik_persandian'             => 'Statistik & Persandian',
    ];

    // ── Index ─────────────────────────────────────────────────────

    public function index()
    {
        $kategoris = KategoriSistem::with(['nodes.knowledgeBase', 'nodes.sopInternal', 'nodes.bidang', 'nodes.nextYa', 'nodes.nextTidak'])
            ->orderBy('nama_kategori')
            ->get();

        $bidangs  = Bidang::all();
        $articles = KnowledgeBase::where('visibilitas_akses', 'opd')
            ->where('status_publikasi', 'published')
            ->orderBy('nama_artikel_sop')
            ->get(['id', 'kategori_artikel_id', 'nama_artikel_sop']);

        $internalArticles = KnowledgeBase::where('visibilitas_akses', 'internal')
            ->where('status_publikasi', 'published')
            ->orderBy('nama_artikel_sop')
            ->get(['id', 'bidang_id', 'nama_artikel_sop']);

        $kategorisData = $kategoris->map(fn($k) => $this->formatKategori($k))->values();
        $bidangsData   = $bidangs->map(fn($b) => [
            'id'    => $b->id,
            'label' => $this->bidangLabel[$b->nama_bidang] ?? $b->nama_bidang,
        ])->values();
        $articlesData = $articles->map(fn($a) => [
            'id'                  => $a->id,
            'kategori_artikel_id' => $a->kategori_artikel_id ?? '',
            'judul'               => $a->nama_artikel_sop,
        ])->values();
        $internalArticlesData = $internalArticles->map(fn($a) => [
            'id'       => $a->id,
            'bidang_id' => $a->bidang_id ?? '',
            'judul'    => $a->nama_artikel_sop,
        ])->values();

        return view('super_admin.konfigurasiSistem', compact(
            'kategorisData', 'bidangsData', 'articlesData', 'internalArticlesData'
        ));
    }

    // ── Kategori CRUD (JSON) ───────────────────────────────────────

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'icon'          => 'nullable|string|in:' . implode(',', array_keys(config('category_icons.presets', []))),
        ]);

        $k = KategoriSistem::create([
            'id'            => (string) Str::uuid(),
            'nama_kategori' => $request->nama_kategori,
            'deskripsi'     => $request->deskripsi,
            'icon'          => $request->icon ?? 'default',
        ]);

        return response()->json($this->formatKategori($k));
    }

    public function updateKategori(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'icon'          => 'nullable|string|in:' . implode(',', array_keys(config('category_icons.presets', []))),
        ]);

        $k = KategoriSistem::findOrFail($id);
        $k->update([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi'     => $request->deskripsi,
            'icon'          => $request->icon ?? $k->icon ?? 'default',
        ]);

        return response()->json([
            'id'            => $k->id,
            'nama_kategori' => $k->nama_kategori ?? '',
            'deskripsi'     => $k->deskripsi ?? '',
        ]);
    }

    public function destroyKategori($id)
    {
        $nodeIds = NodeDiagnosis::where('kategori_id', $id)->pluck('id')->toArray();

        if (!empty($nodeIds)) {
            NodeDiagnosis::whereIn('id_next_ya', $nodeIds)->update(['id_next_ya' => null]);
            NodeDiagnosis::whereIn('id_next_tidak', $nodeIds)->update(['id_next_tidak' => null]);
        }

        NodeDiagnosis::where('kategori_id', $id)->delete();

        KategoriSistem::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    // ── Node CRUD (JSON) ──────────────────────────────────────────

    public function storeNode(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori_sistem,id',
            'tipe_node'   => 'required|in:pertanyaan,solusi',
        ]);

        $isSolusi = $request->tipe_node === 'solusi';

        $n = NodeDiagnosis::create([
            'id'                => (string) Str::uuid(),
            'kategori_id'       => $request->kategori_id,
            'tipe_node'         => $request->tipe_node,
            'teks_pertanyaan'   => !$isSolusi ? $request->teks_pertanyaan : null,
            'hint_konteks'      => $request->hint_konteks ?: null,
            'judul_solusi'      => $isSolusi ? $request->judul_solusi : null,
            'penjelasan_solusi' => $request->penjelasan_solusi ?: null,
            'rekomendasi_penanganan' => $request->rekomendasi_penanganan ?: null,
            'id_next_ya'        => null,
            'id_next_tidak'     => null,
            'kb_id'             => $request->kb_id ?: null,
            'sop_internal_id'   => $isSolusi ? ($request->sop_internal_id ?: null) : null,
            'bidang_id'         => $isSolusi ? ($request->bidang_id ?: null) : null,
        ]);

        $newNodes   = [];
        $deletedIds = [];
        if ($request->tipe_node === 'pertanyaan') {
            $this->applyRouting($n, $request->routing_ya_type, 'ya', $newNodes, $deletedIds);
            $this->applyRouting($n, $request->routing_tidak_type, 'tidak', $newNodes, $deletedIds);
        }

        $n->load(['knowledgeBase', 'sopInternal', 'bidang', 'nextYa', 'nextTidak']);

        $newNodesFormatted = collect($newNodes)
            ->unique('id')
            ->map(fn($node) => $this->formatNode(
                $node->load(['knowledgeBase', 'sopInternal', 'bidang', 'nextYa', 'nextTidak'])
            ))
            ->values();

        return response()->json([
            'node'             => $this->formatNode($n),
            'new_nodes'        => $newNodesFormatted,
            'deleted_node_ids' => $deletedIds,
        ]);
    }

    public function updateNode(Request $request, $id)
    {
        $n = NodeDiagnosis::findOrFail($id);
        $isSolusi = $request->tipe_node === 'solusi';
        $n->update([
            'tipe_node'         => $request->tipe_node,
            'teks_pertanyaan'   => !$isSolusi ? $request->teks_pertanyaan : null,
            'hint_konteks'      => $request->hint_konteks ?: null,
            'judul_solusi'      => $isSolusi ? $request->judul_solusi : null,
            'penjelasan_solusi' => $request->penjelasan_solusi ?: null,
            'rekomendasi_penanganan' => $request->rekomendasi_penanganan ?: null,
            'kb_id'             => $request->kb_id ?: null,
            'sop_internal_id'   => $isSolusi ? ($request->sop_internal_id ?: null) : null,
            'bidang_id'         => $isSolusi ? ($request->bidang_id ?: null) : null,
        ]);

        $newNodes   = [];
        $deletedIds = [];

        if ($request->tipe_node === 'pertanyaan') {
            $this->applyRouting($n, $request->routing_ya_type,    'ya',    $newNodes, $deletedIds);
            $this->applyRouting($n, $request->routing_tidak_type, 'tidak', $newNodes, $deletedIds);
        } else {
            if ($n->id_next_ya) {
                $child = NodeDiagnosis::find($n->id_next_ya);
                if ($child) $this->deleteNodeCascade($child, $deletedIds);
                $n->update(['id_next_ya' => null]);
            }
            if ($n->id_next_tidak) {
                $child = NodeDiagnosis::find($n->id_next_tidak);
                if ($child) $this->deleteNodeCascade($child, $deletedIds);
                $n->update(['id_next_tidak' => null]);
            }
        }

        $n->load(['knowledgeBase', 'sopInternal', 'bidang', 'nextYa', 'nextTidak']);

        $newNodesFormatted = collect($newNodes)
            ->unique('id')
            ->map(fn($node) => $this->formatNode(
                $node->load(['knowledgeBase', 'sopInternal', 'bidang', 'nextYa', 'nextTidak'])
            ))
            ->values();

        return response()->json([
            'node'             => $this->formatNode($n),
            'new_nodes'        => $newNodesFormatted,
            'deleted_node_ids' => array_values(array_unique($deletedIds)),
        ]);
    }

    private function applyRouting(
        NodeDiagnosis $parent,
        ?string $routingType,
        string $branch,
        array &$newNodes,
        array &$deletedIds
    ): void {
        $field   = $branch === 'ya' ? 'id_next_ya' : 'id_next_tidak';
        $oldId   = $parent->$field;
        $oldNode = $oldId ? NodeDiagnosis::find($oldId) : null;

        switch ($routingType) {
            case 'pertanyaan':
                if ($oldNode && $oldNode->tipe_node === 'pertanyaan') {
                    // sudah terhubung — biarkan
                } else {
                    if ($oldNode) $this->deleteNodeCascade($oldNode, $deletedIds);
                    $child      = $this->createChildQuestion($parent->kategori_id);
                    $newNodes[] = $child;
                    $parent->update([$field => $child->id]);
                }
                break;

            case 'solusi':
                if ($oldNode && $oldNode->tipe_node === 'solusi') {
                    // sudah terhubung — biarkan
                } else {
                    if ($oldNode) $this->deleteNodeCascade($oldNode, $deletedIds);
                    $child      = $this->createChildSolusi($parent->kategori_id);
                    $newNodes[] = $child;
                    $parent->update([$field => $child->id]);
                }
                break;

            default:
                if ($oldNode) $this->deleteNodeCascade($oldNode, $deletedIds);
                $parent->update([$field => null]);
        }
    }

    private function createChildQuestion(string $kategoriId): NodeDiagnosis
    {
        return NodeDiagnosis::create([
            'id'          => (string) Str::uuid(),
            'kategori_id' => $kategoriId,
            'tipe_node'   => 'pertanyaan',
        ]);
    }

    private function createChildSolusi(string $kategoriId): NodeDiagnosis
    {
        return NodeDiagnosis::create([
            'id'          => (string) Str::uuid(),
            'kategori_id' => $kategoriId,
            'tipe_node'   => 'solusi',
        ]);
    }

    private function deleteNodeCascade(NodeDiagnosis $node, array &$deletedIds): void
    {
        if ($node->id_next_ya) {
            $child = NodeDiagnosis::find($node->id_next_ya);
            if ($child) $this->deleteNodeCascade($child, $deletedIds);
        }
        if ($node->id_next_tidak) {
            $child = NodeDiagnosis::find($node->id_next_tidak);
            if ($child) $this->deleteNodeCascade($child, $deletedIds);
        }
        NodeDiagnosis::where('id_next_ya',    $node->id)->update(['id_next_ya'    => null]);
        NodeDiagnosis::where('id_next_tidak', $node->id)->update(['id_next_tidak' => null]);
        $deletedIds[] = $node->id;
        $node->delete();
    }

    public function destroyNode($id)
    {
        $node       = NodeDiagnosis::findOrFail($id);
        $deletedIds = [];
        $this->deleteNodeCascade($node, $deletedIds);

        return response()->json([
            'success'          => true,
            'deleted_node_ids' => array_values(array_unique($deletedIds)),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function formatKategori(KategoriSistem $k): array
    {
        $nodes = $k->relationLoaded('nodes') ? $k->nodes : collect();

        $hasIncompleteSolusi = $nodes->contains(
            fn($n) => $n->tipe_node === 'solusi' && (empty($n->kb_id) || empty($n->bidang_id))
        ) || $nodes->contains(
            fn($n) => $n->tipe_node === 'pertanyaan' && (
                empty($n->teks_pertanyaan) ||
                empty($n->id_next_ya)      ||
                empty($n->id_next_tidak)
            )
        );

        return [
            'id'                    => $k->id,
            'nama_kategori'         => $k->nama_kategori ?? '',
            'deskripsi'             => $k->deskripsi ?? '',
            'icon'                  => $k->icon ?? 'default',
            'has_incomplete_solusi' => $hasIncompleteSolusi,
            'nodes'                 => $nodes->map(fn($n) => $this->formatNode($n))->values(),
        ];
    }

    private function formatNode(NodeDiagnosis $n): array
    {
        $yaType    = $n->nextYa?->tipe_node ?? '';
        $tidakType = $n->nextTidak?->tipe_node ?? '';

        return [
            'id'                    => $n->id,
            'kode'                  => ($n->tipe_node === 'pertanyaan' ? 'Q' : 'A') . '-' . strtoupper(substr($n->id, 0, 4)),
            'kategori_id'           => $n->kategori_id ?? '',
            'tipe_node'             => $n->tipe_node,
            'teks_pertanyaan'       => $n->teks_pertanyaan ?? '',
            'hint_konteks'          => $n->hint_konteks ?? '',
            'judul_solusi'          => $n->judul_solusi ?? '',
            'penjelasan_solusi'     => $n->penjelasan_solusi ?? '',
            'rekomendasi_penanganan' => $n->rekomendasi_penanganan ?? '',
            'id_next_ya'            => $n->id_next_ya ?? '',
            'id_next_tidak'         => $n->id_next_tidak ?? '',
            'kb_id'                 => $n->kb_id ?? '',
            'kb_judul'              => $n->knowledgeBase?->nama_artikel_sop ?? '',
            'sop_internal_id'       => $n->sop_internal_id ?? '',
            'sop_judul'             => $n->sopInternal?->nama_artikel_sop ?? '',
            'bidang_id'             => $n->bidang_id ?? '',
            'bidang_nama'           => $this->bidangLabel[$n->bidang?->nama_bidang ?? ''] ?? '',
            'routing_ya_type'           => $yaType,
            'routing_ya_child_kode'     => $n->nextYa
                                            ? (($yaType === 'pertanyaan' ? 'Q-' : 'A-') . strtoupper(substr($n->nextYa->id, 0, 4)))
                                            : '',
            'routing_ya_child_text'     => $n->nextYa
                                            ? ($yaType === 'pertanyaan'
                                                ? ($n->nextYa->teks_pertanyaan ?? '')
                                                : ($n->nextYa->judul_solusi ?? ''))
                                            : '',
            'routing_tidak_type'        => $tidakType,
            'routing_tidak_child_kode'  => $n->nextTidak
                                            ? (($tidakType === 'pertanyaan' ? 'Q-' : 'A-') . strtoupper(substr($n->nextTidak->id, 0, 4)))
                                            : '',
            'routing_tidak_child_text'  => $n->nextTidak
                                            ? ($tidakType === 'pertanyaan'
                                                ? ($n->nextTidak->teks_pertanyaan ?? '')
                                                : ($n->nextTidak->judul_solusi ?? ''))
                                            : '',
        ];
    }
}
