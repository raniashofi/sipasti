<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use App\Models\Kategori;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseController extends Controller
{
    private array $bidangLabel = [
        'e_government'                     => 'E-Government',
        'infrastruktur_teknologi_informasi' => 'Infrastruktur TI',
        'statistik_persandian'             => 'Statistik & Persandian',
    ];

    // ── List OPD ──────────────────────────────────────────────────

    public function indexOpd(Request $request)
    {
        return $this->listView($request, 'opd');
    }

    // ── List Internal ─────────────────────────────────────────────

    public function indexInternal(Request $request)
    {
        return $this->listView($request, 'internal');
    }

    private function listView(Request $request, string $visibility)
    {
        $search        = $request->query('search', '');
        $kategoriFilter = $request->query('kategori_id', '');
        $statusFilter  = $request->query('status', '');

        $query = KnowledgeBase::with('kategori.bidang', 'tags')
            ->where('visibilitas_akses', $visibility);

        if ($search)        { $query->where('nama_artikel_sop', 'like', "%{$search}%"); }
        if ($kategoriFilter){ $query->where('kategori_id', $kategoriFilter); }
        if ($statusFilter)  { $query->where('status_publikasi', $statusFilter); }

        $articles = $query->orderByDesc('created_at')->get();
        $kategoris = Kategori::with('bidang')->orderBy('nama_kategori')->get();

        return view('super_admin.pustaka.index', compact(
            'articles', 'kategoris', 'visibility',
            'search', 'kategoriFilter', 'statusFilter'
        ))->with('bidangLabel', $this->bidangLabel);
    }

    // ── Create ────────────────────────────────────────────────────

    public function create(Request $request)
    {
        // Ensure only super admin can create articles
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat membuat artikel baru.');
        }

        $kategoris  = Kategori::with('bidang')->orderBy('nama_kategori')->get();
        $visibility = $request->query('visibility', 'opd');

        return view('super_admin.pustaka.form', [
            'article'    => null,
            'kategoris'  => $kategoris,
            'visibility' => $visibility,
            'bidangLabel' => $this->bidangLabel,
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'nama_artikel_sop'  => 'required|string|max:500',
            'isi_konten'        => 'nullable|string',
            'deskripsi_singkat' => 'nullable|string|max:500',
            'status_publikasi'  => 'required|in:draft,published',
            'visibilitas_akses' => 'required|in:opd,internal',
            'kategori_id'       => 'nullable|exists:kategori,id',
            'header_image'      => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'lampiran_file'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,jpg,jpeg,png|max:10240',
        ], [
            'nama_artikel_sop.required'  => 'Judul artikel wajib diisi.',
            'status_publikasi.required'  => 'Status publikasi wajib dipilih.',
            'visibilitas_akses.required' => 'Visibilitas akses wajib dipilih.',
            'header_image.image'         => 'File header harus berupa gambar.',
            'header_image.max'           => 'Ukuran header maksimal 10 MB.',
            'lampiran_file.max'          => 'Ukuran lampiran maksimal 10 MB.',
        ]);

        $id = (string) Str::uuid();
        $articlePath = "knowledge_base/{$id}";

        // ── Handle file uploads ──
        $headerPath   = null;
        $lampiranPath = null;

        if ($request->hasFile('header_image')) {
            $filename   = 'header_' . time() . '.' . $request->file('header_image')->getClientOriginalExtension();
            $headerPath = Storage::disk('public')->putFileAs($articlePath, $request->file('header_image'), $filename);
        }

        if ($request->hasFile('lampiran_file')) {
            $filename     = 'lampiran_' . time() . '.' . $request->file('lampiran_file')->getClientOriginalExtension();
            $lampiranPath = Storage::disk('public')->putFileAs($articlePath, $request->file('lampiran_file'), $filename);
        }

        KnowledgeBase::create([
            'id'                => $id,
            'kategori_id'       => $request->kategori_id ?: null,
            'nama_artikel_sop'  => $request->nama_artikel_sop,
            'isi_konten'        => $this->sanitizeHtml($request->isi_konten),
            'deskripsi_singkat' => $request->deskripsi_singkat,
            'status_publikasi'  => $request->status_publikasi,
            'visibilitas_akses' => $request->visibilitas_akses,
            'header_image'      => $headerPath,
            'lampiran_file'     => $lampiranPath,
            'total_views'       => 0,
        ]);

        $this->syncTags($id, $request->input('tags_raw', ''));

        $route = $request->visibilitas_akses === 'opd'
            ? 'super_admin.pustaka.opd'
            : 'super_admin.pustaka.internal';

        return redirect()->route($route)->with('success', 'Artikel berhasil ditambahkan.');
    }

    // ── Edit ──────────────────────────────────────────────────────

    public function edit($id)
    {
        // Ensure only super admin can edit articles
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat mengedit artikel.');
        }

        $article   = KnowledgeBase::with('kategori', 'tags')->findOrFail($id);
        $kategoris = Kategori::with('bidang')->orderBy('nama_kategori')->get();

        return view('super_admin.pustaka.form', [
            'article'    => $article,
            'kategoris'  => $kategoris,
            'visibility' => $article->visibilitas_akses,
            'bidangLabel' => $this->bidangLabel,
        ]);
    }

    // ── Update ────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        // Ensure only super admin can update articles
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat mengubah artikel.');
        }

        $article = KnowledgeBase::findOrFail($id);

        $request->validate([
            'nama_artikel_sop'  => 'required|string|max:500',
            'isi_konten'        => 'nullable|string',
            'deskripsi_singkat' => 'nullable|string|max:500',
            'status_publikasi'  => 'required|in:draft,published',
            'visibilitas_akses' => 'required|in:opd,internal',
            'kategori_id'       => 'nullable|exists:kategori,id',
            'header_image'      => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'lampiran_file'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,jpg,jpeg,png|max:10240',
        ], [
            'nama_artikel_sop.required'  => 'Judul artikel wajib diisi.',
            'status_publikasi.required'  => 'Status publikasi wajib dipilih.',
            'visibilitas_akses.required' => 'Visibilitas akses wajib dipilih.',
            'header_image.image'         => 'File header harus berupa gambar.',
            'header_image.max'           => 'Ukuran header maksimal 10 MB.',
            'lampiran_file.max'          => 'Ukuran lampiran maksimal 10 MB.',
        ]);

        $articlePath = "knowledge_base/{$id}";
        $updateData  = [
            'kategori_id'       => $request->kategori_id ?: null,
            'nama_artikel_sop'  => $request->nama_artikel_sop,
            'isi_konten'        => $this->sanitizeHtml($request->isi_konten),
            'deskripsi_singkat' => $request->deskripsi_singkat,
            'status_publikasi'  => $request->status_publikasi,
            'visibilitas_akses' => $request->visibilitas_akses,
        ];

        // ── Handle header update ──
        if ($request->hasFile('header_image')) {
            if ($article->header_image && Storage::disk('public')->exists($article->header_image)) {
                Storage::disk('public')->delete($article->header_image);
            }
            $filename = 'header_' . time() . '.' . $request->file('header_image')->getClientOriginalExtension();
            $updateData['header_image'] = Storage::disk('public')->putFileAs($articlePath, $request->file('header_image'), $filename);
        }

        // ── Handle lampiran update ──
        if ($request->hasFile('lampiran_file')) {
            if ($article->lampiran_file && Storage::disk('public')->exists($article->lampiran_file)) {
                Storage::disk('public')->delete($article->lampiran_file);
            }
            $filename = 'lampiran_' . time() . '.' . $request->file('lampiran_file')->getClientOriginalExtension();
            $updateData['lampiran_file'] = Storage::disk('public')->putFileAs($articlePath, $request->file('lampiran_file'), $filename);
        }

        $article->update($updateData);
        $this->syncTags($id, $request->input('tags_raw', ''));

        $route = $article->visibilitas_akses === 'opd'
            ? 'super_admin.pustaka.opd'
            : 'super_admin.pustaka.internal';

        return redirect()->route($route)->with('success', 'Artikel berhasil diperbarui.');
    }

    // ── Destroy ───────────────────────────────────────────────────

    public function destroy($id)
    {
        // Ensure only super admin can delete articles
        if (Auth::user()?->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat menghapus artikel.');
        }

        $article = KnowledgeBase::findOrFail($id);
        $route   = $article->visibilitas_akses === 'opd'
            ? 'super_admin.pustaka.opd'
            : 'super_admin.pustaka.internal';

        // ── Delete files ──
        $filesToDelete = [
            $article->header_image,
            $article->lampiran_file,
        ];

        foreach (array_filter($filesToDelete) as $file) {
            if (Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        }

        // ── Delete directory if empty ──
        $articlePath = "knowledge_base/{$id}";
        if (Storage::disk('public')->exists($articlePath)) {
            $files = Storage::disk('public')->files($articlePath);
            if (empty($files)) {
                Storage::disk('public')->deleteDirectory($articlePath);
            }
        }

        // ── Delete related data ──
        // Tags are automatically deleted via cascade delete on pivot table
        $article->delete();

        return redirect()->route($route)->with('success', 'Artikel berhasil dihapus.');
    }

    // ── Preview ───────────────────────────────────────────────────

    public function preview($id)
    {
        $article = KnowledgeBase::with('kategori', 'tags')->findOrFail($id);

        // Check authorization - only super admin or public view allowed
        if (Auth::user() && Auth::user()?->role !== 'super_admin') {
            if ($article->visibilitas_akses === 'internal') {
                abort(403, 'Artikel internal tidak bisa diakses.');
            }
        }

        return view('super_admin.pustaka.preview', compact('article'));
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function syncTags(string $kbId, string $raw): void
    {
        $article = KnowledgeBase::findOrFail($kbId);
        $tagNames = array_filter(array_map('trim', explode(',', $raw)));
        $tagIds = [];

        foreach ($tagNames as $name) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $tag = Tag::firstOrCreate(
                ['slug' => $slug],
                ['id' => (string) Str::uuid(), 'nama_tag' => $name]
            );
            $tagIds[] = $tag->id;
        }

        // Sync tags using pivot table (many-to-many)
        $article->tags()->sync($tagIds);
    }

    private function sanitizeHtml(?string $html): ?string
    {
        if (!$html) return null;

        // Allowed HTML tags dan attributes dari Quill.js
        $allowedTags = [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'blockquote', 'pre', 'code',
            'a', 'img',
            'video', 'iframe',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
        ];

        // Strip tags dengan whitelist
        $filtered = strip_tags($html, '<' . implode('><', $allowedTags) . '>');

        // Remove script tags dan dangerous attributes
        $filtered = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $filtered);
        $filtered = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $filtered);
        $filtered = preg_replace('/on\w+\s*=\s*[^\s>]*/i', '', $filtered);

        return trim($filtered) ?: null;
    }

    /**
     * Upload image for Quill editor
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            $file = $request->file('image');
            $filename = 'quill_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('knowledge_base/inline-images', $file, $filename);

            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload gambar: ' . $e->getMessage(),
            ], 400);
        }
    }
}
