<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ArtikelOpd;
use App\Models\Bidang;
use App\Models\KategoriArtikel;
use App\Models\LampiranArtikel;
use App\Models\SopInternal;
use App\Models\Tag;
use App\Support\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseController extends Controller
{
    // ── Tab OPD: List Kategori (card) ────────────────────────────

    public function indexOpd()
    {
        $kategoris = KategoriArtikel::withCount(['knowledgeBases as artikel_count' => function ($q) {
            $q->where('status_publikasi', 'published')
                ->orWhere('status_publikasi', 'draft');
        }])->orderBy('nama_kategori')->get();

        return view('super_admin.pustaka.index', [
            'tab'      => 'opd',
            'kategoris' => $kategoris,
            'bidangs'  => collect(),
        ]);
    }

    // ── Tab OPD: Artikel per Kategori ────────────────────────────

    public function opdKategori(Request $request, $id)
    {
        $kategori     = KategoriArtikel::findOrFail($id);
        $search       = $request->query('search', '');
        $statusFilter = $request->query('status', '');

        $query = ArtikelOpd::with('tags')
            ->with('lampirans')
            ->where('kategori_artikel_id', $id);

        if ($search) {
            $query->where(fn ($q) =>
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhereHas('tags', fn ($qt) => $qt->where('nama_tag', 'like', "%{$search}%"))
            );
        }
        if ($statusFilter) {
            $query->where('status_publikasi', $statusFilter);
        }

        $articles = $query->orderByDesc('created_at')->get();

        return view('super_admin.pustaka.opd-kategori', compact(
            'kategori', 'articles', 'search', 'statusFilter'
        ));
    }

    // ── Tab Internal: List Bidang (card) ─────────────────────────

    public function indexInternal()
    {
        $bidangs = Bidang::withCount(['sopInternal as artikel_count'])->get();

        return view('super_admin.pustaka.index', [
            'tab'      => 'internal',
            'kategoris' => collect(),
            'bidangs'  => $bidangs,
        ]);
    }

    // ── Tab Internal: Artikel per Bidang ─────────────────────────

    public function internalBidang(Request $request, $id)
    {
        $bidang       = Bidang::findOrFail($id);
        $search       = $request->query('search', '');
        $statusFilter = $request->query('status', '');

        $query = SopInternal::with('tags')
            ->with('lampirans')
            ->where('bidang_id', $id);

        if ($search) {
            $query->where(fn ($q) =>
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhereHas('tags', fn ($qt) => $qt->where('nama_tag', 'like', "%{$search}%"))
            );
        }
        if ($statusFilter) {
            $query->where('status_publikasi', $statusFilter);
        }

        $articles = $query->orderByDesc('created_at')->get();

        return view('super_admin.pustaka.internal-bidang', compact(
            'bidang', 'articles', 'search', 'statusFilter'
        ));
    }

    // ── CRUD Kategori Artikel ─────────────────────────────────────

    public function storeKategori(Request $request)
    {
        $request->validate(['nama_kategori' => 'required|string|max:255']);

        KategoriArtikel::create([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi'     => $request->deskripsi,
        ]);

        return redirect()->route('super_admin.pustaka.opd')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateKategori(Request $request, $id)
    {
        $request->validate(['nama_kategori' => 'required|string|max:255']);

        KategoriArtikel::findOrFail($id)->update([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi'     => $request->deskripsi,
        ]);

        return redirect()->route('super_admin.pustaka.opd')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kat = KategoriArtikel::withCount('knowledgeBases')->findOrFail($id);

        if ($kat->knowledge_bases_count > 0) {
            return redirect()->route('super_admin.pustaka.opd')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki artikel.');
        }

        $kat->delete();

        return redirect()->route('super_admin.pustaka.opd')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    // ── CRUD Bidang ───────────────────────────────────────────────

    public function storeBidang(Request $request)
    {
        $request->validate([
            'nama_bidang' => 'required|string|max:255',
            'batas_hari_pengerjaan' => 'nullable|integer|min:1|max:365',
        ]);

        Bidang::create([
            'nama_bidang'           => $request->nama_bidang,
            'batas_hari_pengerjaan' => $request->batas_hari_pengerjaan ?: 3,
        ]);

        return redirect()->route('super_admin.pustaka.internal')
            ->with('success', 'Bidang berhasil ditambahkan.');
    }

    public function updateBidang(Request $request, $id)
    {
        $request->validate([
            'nama_bidang' => 'required|string|max:255',
            'batas_hari_pengerjaan' => 'nullable|integer|min:1|max:365',
        ]);

        Bidang::findOrFail($id)->update([
            'nama_bidang'           => $request->nama_bidang,
            'batas_hari_pengerjaan' => $request->batas_hari_pengerjaan ?: ($request->batas_hari_pengerjaan ?? 3),
        ]);

        return redirect()->route('super_admin.pustaka.internal')
            ->with('success', 'Bidang berhasil diperbarui.');
    }

    public function destroyBidang($id)
    {
        $bidang = Bidang::withCount(['sopInternal as artikel_count'])->findOrFail($id);

        if ($bidang->artikel_count > 0) {
            return redirect()->route('super_admin.pustaka.internal')
                ->with('error', 'Bidang tidak dapat dihapus karena masih memiliki artikel.');
        }

        $bidang->delete();

        return redirect()->route('super_admin.pustaka.internal')
            ->with('success', 'Bidang berhasil dihapus.');
    }

    // ── Create Artikel ────────────────────────────────────────────

    public function create(Request $request)
    {
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat membuat artikel baru.');
        }

        $visibility  = $request->query('visibility', 'opd');
        $kategoriId  = $request->query('kategori_id');
        $bidangId    = $request->query('bidang_id');
        $kategoris   = KategoriArtikel::orderBy('nama_kategori')->get();
        $bidangs     = Bidang::orderBy('nama_bidang')->get();

        return view('super_admin.pustaka.form', [
            'article'    => null,
            'kategoris'  => $kategoris,
            'bidangs'    => $bidangs,
            'visibility' => $visibility,
            'kategoriId' => $kategoriId,
            'bidangId'   => $bidangId,
        ]);
    }

    // ── Store Artikel ─────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'nama_artikel_sop'    => 'required|string|max:500',
            'isi_konten'          => 'nullable|string|max:4000000',
            'deskripsi_singkat'   => 'nullable|string|max:500',
            'status_publikasi'    => 'required|in:draft,published',
            'visibilitas_akses'   => 'required|in:opd,internal',
            'kategori_artikel_id' => 'required_if:visibilitas_akses,opd|nullable|exists:kategori_artikel,id',
            'bidang_id'           => 'required_if:visibilitas_akses,internal|nullable|exists:bidang,id',
            'header_image'        => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'lampiran_files'      => 'nullable|array|max:5',
            'lampiran_files.*'    => 'file|mimes:pdf,doc,docx,xls,xlsx,txt,jpg,jpeg,png|max:5120',
        ], [
            'nama_artikel_sop.required'  => 'Judul artikel wajib diisi.',
            'status_publikasi.required'  => 'Status publikasi wajib dipilih.',
            'visibilitas_akses.required' => 'Visibilitas akses wajib dipilih.',
            'kategori_artikel_id.required_if' => 'Kategori artikel wajib dipilih untuk artikel OPD.',
            'bidang_id.required_if' => 'Bidang wajib dipilih untuk SOP internal.',
            'isi_konten.max'             => 'Konten artikel terlalu besar (max ~4MB). Gunakan fitur Upload Gambar daripada menyalin-tempel gambar dari internet.',
            'header_image.image'         => 'File header harus berupa gambar.',
            'header_image.mimes'         => 'Format header harus JPG atau PNG.',
            'header_image.max'           => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
            'lampiran_files.max'         => 'Maksimal 5 file lampiran.',
            'lampiran_files.*.file'      => 'Lampiran harus berupa file yang valid.',
            'lampiran_files.*.mimes'     => 'Format lampiran: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG.',
            'lampiran_files.*.max'       => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
        ]);

        $isOpd       = $request->visibilitas_akses === 'opd';
        $id          = IdGenerator::make($isOpd ? 'ART' : 'SOP');
        $articlePath = "knowledge_base/{$id}";
        $headerPath  = null;

        if ($request->hasFile('header_image') && $request->file('header_image')->isValid()) {
            $filename   = 'header_' . time() . '.' . $request->file('header_image')->getClientOriginalExtension();
            $headerPath = Storage::disk('public')->putFileAs($articlePath, $request->file('header_image'), $filename);
        }

        $modelClass = $isOpd ? ArtikelOpd::class : SopInternal::class;
        $modelClass::create([
            'id'                  => $id,
            'kategori_artikel_id' => $isOpd ? ($request->kategori_artikel_id ?: null) : null,
            'bidang_id'           => !$isOpd ? ($request->bidang_id ?: null) : null,
            'judul'               => $request->nama_artikel_sop,
            'isi_konten'          => $this->sanitizeHtml($request->isi_konten),
            'deskripsi_singkat'   => $request->deskripsi_singkat,
            'status_publikasi'    => $request->status_publikasi,
            'header_image'        => $headerPath,
            'total_views'         => 0,
        ]);

        $this->storeLampirans($request, $id, $isOpd, $articlePath);
        $this->syncTags($id, $request->visibilitas_akses, $request->input('tags_raw') ?? '');

        return redirect()->to($this->backUrl($request->visibilitas_akses, $request->kategori_artikel_id, $request->bidang_id))
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    // ── Edit Artikel ──────────────────────────────────────────────

    public function edit($id)
    {
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat mengedit artikel.');
        }

        $article   = $this->findArticleOrFail($id);
        $kategoris = KategoriArtikel::orderBy('nama_kategori')->get();
        $bidangs   = Bidang::orderBy('nama_bidang')->get();

        return view('super_admin.pustaka.form', [
            'article'    => $article,
            'kategoris'  => $kategoris,
            'bidangs'    => $bidangs,
            'visibility' => $article->visibilitas_akses,
            'kategoriId' => $article->kategori_artikel_id,
            'bidangId'   => $article->bidang_id,
        ]);
    }

    // ── Update Artikel ────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat mengubah artikel.');
        }

        $article = $this->findArticleOrFail($id);

        $request->validate([
            'nama_artikel_sop'    => 'required|string|max:500',
            'isi_konten'          => 'nullable|string|max:4000000',
            'deskripsi_singkat'   => 'nullable|string|max:500',
            'status_publikasi'    => 'required|in:draft,published',
            'visibilitas_akses'   => 'required|in:opd,internal',
            'kategori_artikel_id' => 'required_if:visibilitas_akses,opd|nullable|exists:kategori_artikel,id',
            'bidang_id'           => 'required_if:visibilitas_akses,internal|nullable|exists:bidang,id',
            'header_image'        => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'lampiran_files'      => 'nullable|array|max:5',
            'lampiran_files.*'    => 'file|mimes:pdf,doc,docx,xls,xlsx,txt,jpg,jpeg,png|max:5120',
            'remove_lampiran_ids' => 'nullable|array',
            'remove_lampiran_ids.*' => 'string|exists:lampiran_artikel,id',
        ], [
            'nama_artikel_sop.required'  => 'Judul artikel wajib diisi.',
            'status_publikasi.required'  => 'Status publikasi wajib dipilih.',
            'visibilitas_akses.required' => 'Visibilitas akses wajib dipilih.',
            'kategori_artikel_id.required_if' => 'Kategori artikel wajib dipilih untuk artikel OPD.',
            'bidang_id.required_if' => 'Bidang wajib dipilih untuk SOP internal.',
            'isi_konten.max'             => 'Konten artikel terlalu besar (max ~4MB). Gunakan fitur Upload Gambar daripada menyalin-tempel gambar dari internet.',
            'header_image.image'         => 'File header harus berupa gambar.',
            'header_image.mimes'         => 'Format header harus JPG atau PNG.',
            'header_image.max'           => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
            'lampiran_files.max'         => 'Maksimal 5 file lampiran.',
            'lampiran_files.*.file'      => 'Lampiran harus berupa file yang valid.',
            'lampiran_files.*.mimes'     => 'Format lampiran: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG.',
            'lampiran_files.*.max'       => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
        ]);

        $articlePath = "knowledge_base/{$id}";
        $isOpd       = $request->visibilitas_akses === 'opd';

        $updateData = [
            'kategori_artikel_id' => $isOpd ? ($request->kategori_artikel_id ?: null) : null,
            'bidang_id'           => !$isOpd ? ($request->bidang_id ?: null) : null,
            'judul'               => $request->nama_artikel_sop,
            'isi_konten'          => $this->sanitizeHtml($request->isi_konten),
            'deskripsi_singkat'   => $request->deskripsi_singkat,
            'status_publikasi'    => $request->status_publikasi,
        ];

        if ($request->hasFile('header_image') && $request->file('header_image')->isValid()) {
            if ($article->header_image && Storage::disk('public')->exists($article->header_image)) {
                Storage::disk('public')->delete($article->header_image);
            }
            $filename = 'header_' . time() . '.' . $request->file('header_image')->getClientOriginalExtension();
            $updateData['header_image'] = Storage::disk('public')->putFileAs($articlePath, $request->file('header_image'), $filename);
        }

        $article->update($updateData);
        $this->deleteLampirans($request->input('remove_lampiran_ids', []), $article);
        $this->storeLampirans($request, $id, $isOpd, $articlePath);
        $this->syncTags($id, $request->visibilitas_akses, $request->input('tags_raw') ?? '');

        return redirect()->to($this->backUrl($request->visibilitas_akses, $request->kategori_artikel_id, $request->bidang_id))
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    // ── Destroy Artikel ───────────────────────────────────────────

    public function destroy($id)
    {
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat menghapus artikel.');
        }

        $article = $this->findArticleOrFail($id);

        $backUrl = $this->backUrl(
            $article->visibilitas_akses,
            $article->kategori_artikel_id,
            $article->bidang_id
        );

        foreach (array_filter([$article->header_image]) as $file) {
            if (Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        }
        foreach ($article->lampirans as $lampiran) {
            if (Storage::disk('public')->exists($lampiran->path_file)) {
                Storage::disk('public')->delete($lampiran->path_file);
            }
        }

        $articlePath = "knowledge_base/{$id}";
        if (Storage::disk('public')->exists($articlePath)) {
            if (empty(Storage::disk('public')->files($articlePath))) {
                Storage::disk('public')->deleteDirectory($articlePath);
            }
        }

        $article->forceDelete();

        return redirect()->to($backUrl)->with('success', 'Artikel berhasil dihapus.');
    }

    // ── Preview ───────────────────────────────────────────────────

    public function preview($id)
    {
        $article = $this->findArticleOrFail($id);

        if (Auth::user() && Auth::user()?->role !== 'super_admin') {
            if ($article->visibilitas_akses === 'internal') {
                abort(403, 'Artikel internal tidak bisa diakses.');
            }
        }

        return view('super_admin.pustaka.preview', compact('article'));
    }

    // ── Upload Inline Image ───────────────────────────────────────

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'image.max' => 'Gambar yang diupload terlalu besar. Maksimal 5 MB.',
        ]);

        try {
            $file     = $request->file('image');
            $filename = 'quill_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path     = Storage::disk('public')->putFileAs('knowledge_base/inline-images', $file, $filename);

            return response()->json([
                'success' => true,
                'url'     => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload gambar: ' . $e->getMessage(),
            ], 400);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function backUrl(string $visibility, ?string $kategoriId, ?string $bidangId): string
    {
        if ($visibility === 'opd' && $kategoriId) {
            return route('super_admin.pustaka.opd.kategori', $kategoriId);
        }
        if ($visibility === 'internal' && $bidangId) {
            return route('super_admin.pustaka.internal.bidang', $bidangId);
        }
        return route($visibility === 'opd' ? 'super_admin.pustaka.opd' : 'super_admin.pustaka.internal');
    }

    private function findArticleOrFail(string $id): ArtikelOpd|SopInternal
    {
        $artikelOpd = ArtikelOpd::with('kategoriArtikel', 'tags', 'lampirans')->find($id);
        if ($artikelOpd) {
            return $artikelOpd;
        }

        return SopInternal::with('bidang', 'tags', 'lampirans')->findOrFail($id);
    }

    private function storeLampirans(Request $request, string $articleId, bool $isOpd, string $articlePath): void
    {
        if (!$request->hasFile('lampiran_files')) {
            return;
        }

        $currentMaxOrder = LampiranArtikel::query()
            ->where($isOpd ? 'artikel_opd_id' : 'sop_internal_id', $articleId)
            ->max('urutan') ?? 0;

        foreach ($request->file('lampiran_files', []) as $index => $file) {
            if (!$file->isValid()) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $filename = 'lampiran_' . time() . '_' . Str::random(8) . '.' . $extension;
            $path = Storage::disk('public')->putFileAs($articlePath, $file, $filename);

            LampiranArtikel::create([
                'artikel_opd_id' => $isOpd ? $articleId : null,
                'sop_internal_id' => $isOpd ? null : $articleId,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'tipe_file' => $extension,
                'ukuran_file' => $file->getSize(),
                'urutan' => $currentMaxOrder + $index + 1,
            ]);
        }
    }

    private function deleteLampirans(array $lampiranIds, ArtikelOpd|SopInternal $article): void
    {
        if (empty($lampiranIds)) {
            return;
        }

        $column = $article instanceof ArtikelOpd ? 'artikel_opd_id' : 'sop_internal_id';
        $lampirans = LampiranArtikel::whereIn('id', $lampiranIds)
            ->where($column, $article->id)
            ->get();

        foreach ($lampirans as $lampiran) {
            if (Storage::disk('public')->exists($lampiran->path_file)) {
                Storage::disk('public')->delete($lampiran->path_file);
            }
            $lampiran->delete();
        }
    }

    private function syncTags(string $articleId, string $visibility, ?string $raw): void
    {
        $article  = $visibility === 'opd'
            ? ArtikelOpd::findOrFail($articleId)
            : SopInternal::findOrFail($articleId);
        $raw      = $raw ?? '';
        $tagNames = array_filter(array_map('trim', explode(',', $raw)));
        $tagIds   = [];

        foreach ($tagNames as $name) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $tag  = Tag::firstOrCreate(
                ['slug' => $slug],
                ['nama_tag' => $name]
            );
            $tagIds[] = $tag->id;
        }

        $article->tags()->sync($tagIds);
    }

    private function sanitizeHtml(?string $html): ?string
    {
        if (!$html) return null;

        $allowedTags = [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'blockquote', 'pre', 'code',
            'a', 'img',
            'video', 'iframe',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
        ];

        $filtered = strip_tags($html, '<' . implode('><', $allowedTags) . '>');
        $filtered = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $filtered);
        $filtered = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $filtered);
        $filtered = preg_replace('/on\w+\s*=\s*[^\s>]*/i', '', $filtered);

        return trim($filtered) ?: null;
    }
}
