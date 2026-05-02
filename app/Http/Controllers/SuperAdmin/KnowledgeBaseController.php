<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\KnowledgeBase;
use App\Models\KategoriArtikel;
use App\Models\Tag;
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
            $q->where('visibilitas_akses', 'opd');
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

        $query = KnowledgeBase::with('tags')
            ->where('kategori_artikel_id', $id)
            ->where('visibilitas_akses', 'opd');

        if ($search) {
            $query->where(fn ($q) =>
                $q->where('nama_artikel_sop', 'like', "%{$search}%")
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
        $bidangs = Bidang::withCount(['knowledgeBases as artikel_count' => function ($q) {
            $q->where('visibilitas_akses', 'internal');
        }])->get();

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

        $query = KnowledgeBase::with('tags')
            ->where('bidang_id', $id)
            ->where('visibilitas_akses', 'internal');

        if ($search) {
            $query->where(fn ($q) =>
                $q->where('nama_artikel_sop', 'like', "%{$search}%")
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
            'id'            => (string) Str::uuid(),
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
        $request->validate(['nama_bidang' => 'required|string|max:255']);

        Bidang::create([
            'id'         => (string) Str::uuid(),
            'nama_bidang' => $request->nama_bidang,
        ]);

        return redirect()->route('super_admin.pustaka.internal')
            ->with('success', 'Bidang berhasil ditambahkan.');
    }

    public function updateBidang(Request $request, $id)
    {
        $request->validate(['nama_bidang' => 'required|string|max:255']);

        Bidang::findOrFail($id)->update(['nama_bidang' => $request->nama_bidang]);

        return redirect()->route('super_admin.pustaka.internal')
            ->with('success', 'Bidang berhasil diperbarui.');
    }

    public function destroyBidang($id)
    {
        $bidang = Bidang::withCount(['knowledgeBases as artikel_count' => function ($q) {
            $q->where('visibilitas_akses', 'internal');
        }])->findOrFail($id);

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
            'isi_konten'          => 'nullable|string',
            'deskripsi_singkat'   => 'nullable|string|max:500',
            'status_publikasi'    => 'required|in:draft,published',
            'visibilitas_akses'   => 'required|in:opd,internal',
            'kategori_artikel_id' => 'nullable|exists:kategori_artikel,id',
            'bidang_id'           => 'nullable|exists:bidang,id',
            'header_image'        => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            'lampiran_file'       => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,jpg,jpeg,png|max:20480',
        ], [
            'nama_artikel_sop.required'  => 'Judul artikel wajib diisi.',
            'status_publikasi.required'  => 'Status publikasi wajib dipilih.',
            'visibilitas_akses.required' => 'Visibilitas akses wajib dipilih.',
            'header_image.image'         => 'File header harus berupa gambar.',
            'header_image.mimes'         => 'Format header harus JPG atau PNG.',
            'header_image.max'           => 'Ukuran header maksimal 20 MB.',
            'lampiran_file.file'         => 'Lampiran harus berupa file yang valid.',
            'lampiran_file.mimes'        => 'Format lampiran: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG.',
            'lampiran_file.max'          => 'Ukuran lampiran maksimal 20 MB.',
        ]);

        $id          = (string) Str::uuid();
        $articlePath = "knowledge_base/{$id}";
        $headerPath  = null;
        $lampiranPath = null;

        if ($request->hasFile('header_image') && $request->file('header_image')->isValid()) {
            $filename   = 'header_' . time() . '.' . $request->file('header_image')->getClientOriginalExtension();
            $headerPath = Storage::disk('public')->putFileAs($articlePath, $request->file('header_image'), $filename);
        }

        if ($request->hasFile('lampiran_file') && $request->file('lampiran_file')->isValid()) {
            $filename     = 'lampiran_' . time() . '.' . $request->file('lampiran_file')->getClientOriginalExtension();
            $lampiranPath = Storage::disk('public')->putFileAs($articlePath, $request->file('lampiran_file'), $filename);
        }

        $isOpd = $request->visibilitas_akses === 'opd';

        KnowledgeBase::create([
            'id'                  => $id,
            'kategori_artikel_id' => $isOpd ? ($request->kategori_artikel_id ?: null) : null,
            'bidang_id'           => !$isOpd ? ($request->bidang_id ?: null) : null,
            'nama_artikel_sop'    => $request->nama_artikel_sop,
            'isi_konten'          => $this->sanitizeHtml($request->isi_konten),
            'deskripsi_singkat'   => $request->deskripsi_singkat,
            'status_publikasi'    => $request->status_publikasi,
            'visibilitas_akses'   => $request->visibilitas_akses,
            'header_image'        => $headerPath,
            'lampiran_file'       => $lampiranPath,
            'total_views'         => 0,
        ]);

        $this->syncTags($id, $request->input('tags_raw') ?? '');

        return redirect()->to($this->backUrl($request->visibilitas_akses, $request->kategori_artikel_id, $request->bidang_id))
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    // ── Edit Artikel ──────────────────────────────────────────────

    public function edit($id)
    {
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat mengedit artikel.');
        }

        $article   = KnowledgeBase::with('kategoriArtikel', 'bidang', 'tags')->findOrFail($id);
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

        $article = KnowledgeBase::findOrFail($id);

        $request->validate([
            'nama_artikel_sop'    => 'required|string|max:500',
            'isi_konten'          => 'nullable|string',
            'deskripsi_singkat'   => 'nullable|string|max:500',
            'status_publikasi'    => 'required|in:draft,published',
            'visibilitas_akses'   => 'required|in:opd,internal',
            'kategori_artikel_id' => 'nullable|exists:kategori_artikel,id',
            'bidang_id'           => 'nullable|exists:bidang,id',
            'header_image'        => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            'lampiran_file'       => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,jpg,jpeg,png|max:20480',
        ], [
            'nama_artikel_sop.required'  => 'Judul artikel wajib diisi.',
            'status_publikasi.required'  => 'Status publikasi wajib dipilih.',
            'visibilitas_akses.required' => 'Visibilitas akses wajib dipilih.',
            'header_image.image'         => 'File header harus berupa gambar.',
            'header_image.mimes'         => 'Format header harus JPG atau PNG.',
            'header_image.max'           => 'Ukuran header maksimal 20 MB.',
            'lampiran_file.file'         => 'Lampiran harus berupa file yang valid.',
            'lampiran_file.mimes'        => 'Format lampiran: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG.',
            'lampiran_file.max'          => 'Ukuran lampiran maksimal 20 MB.',
        ]);

        $articlePath = "knowledge_base/{$id}";
        $isOpd       = $request->visibilitas_akses === 'opd';

        $updateData = [
            'kategori_artikel_id' => $isOpd ? ($request->kategori_artikel_id ?: null) : null,
            'bidang_id'           => !$isOpd ? ($request->bidang_id ?: null) : null,
            'nama_artikel_sop'    => $request->nama_artikel_sop,
            'isi_konten'          => $this->sanitizeHtml($request->isi_konten),
            'deskripsi_singkat'   => $request->deskripsi_singkat,
            'status_publikasi'    => $request->status_publikasi,
            'visibilitas_akses'   => $request->visibilitas_akses,
        ];

        if ($request->hasFile('header_image') && $request->file('header_image')->isValid()) {
            if ($article->header_image && Storage::disk('public')->exists($article->header_image)) {
                Storage::disk('public')->delete($article->header_image);
            }
            $filename = 'header_' . time() . '.' . $request->file('header_image')->getClientOriginalExtension();
            $updateData['header_image'] = Storage::disk('public')->putFileAs($articlePath, $request->file('header_image'), $filename);
        }

        if ($request->hasFile('lampiran_file') && $request->file('lampiran_file')->isValid()) {
            if ($article->lampiran_file && Storage::disk('public')->exists($article->lampiran_file)) {
                Storage::disk('public')->delete($article->lampiran_file);
            }
            $filename = 'lampiran_' . time() . '.' . $request->file('lampiran_file')->getClientOriginalExtension();
            $updateData['lampiran_file'] = Storage::disk('public')->putFileAs($articlePath, $request->file('lampiran_file'), $filename);
        }

        $article->update($updateData);
        $this->syncTags($id, $request->input('tags_raw') ?? '');

        return redirect()->to($this->backUrl($request->visibilitas_akses, $request->kategori_artikel_id, $request->bidang_id))
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    // ── Destroy Artikel ───────────────────────────────────────────

    public function destroy($id)
    {
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat menghapus artikel.');
        }

        $article = KnowledgeBase::findOrFail($id);

        $backUrl = $this->backUrl(
            $article->visibilitas_akses,
            $article->kategori_artikel_id,
            $article->bidang_id
        );

        foreach (array_filter([$article->header_image, $article->lampiran_file]) as $file) {
            if (Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
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
        $article = KnowledgeBase::with('kategoriArtikel', 'bidang', 'tags')->findOrFail($id);

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

    private function syncTags(string $kbId, ?string $raw): void
    {
        $article  = KnowledgeBase::findOrFail($kbId);
        $raw      = $raw ?? '';
        $tagNames = array_filter(array_map('trim', explode(',', $raw)));
        $tagIds   = [];

        foreach ($tagNames as $name) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $tag  = Tag::firstOrCreate(
                ['slug' => $slug],
                ['id' => (string) Str::uuid(), 'nama_tag' => $name]
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
