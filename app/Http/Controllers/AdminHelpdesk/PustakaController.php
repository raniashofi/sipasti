<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Http\Controllers\Controller;
use App\Models\AdminHelpdesk;
use App\Models\KategoriArtikel;
use App\Models\SopInternal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PustakaController extends Controller
{
    public function index(Request $request)
    {
        $admin = $this->findAdminProfile();

        $search         = $request->query('search', '');
        $kategoriFilter = $request->query('kategori_id', '');
        $statusFilter   = $request->query('status', '');

        $articles  = $this->queryArticles($admin?->bidang_id, $search, $statusFilter);
        $kategoris = $this->allKategoris();

        return $this->renderView('admin_helpdesk.pustaka.index', compact(
            'articles', 'kategoris', 'admin',
            'search', 'kategoriFilter', 'statusFilter'
        ));
    }

    public function show($id)
    {
        $admin = $this->findAdminProfile();
        $article = $this->findArticleForAdmin($id, $admin?->bidang_id);

        return $this->renderView('admin_helpdesk.pustaka.show', compact('article', 'admin'));
    }

    // ── Protected methods (overridable for unit testing) ─────

    protected function findAdminProfile()
    {
        return AdminHelpdesk::with('bidang')->where('user_id', Auth::id())->first();
    }

    protected function queryArticles(?string $bidangId, string $search, string $statusFilter)
    {
        $query = SopInternal::where('bidang_id', $bidangId);
        if ($search) {
            $query->where(fn($q) =>
                $q->where('judul', 'like', "%{$search}%")
            );
        }
        if ($statusFilter) { $query->where('status_publikasi', $statusFilter); }
        return $query->orderByDesc('created_at')->get();
    }

    protected function allKategoris()
    {
        return KategoriArtikel::orderBy('nama_kategori')->get();
    }

    protected function findArticleForAdmin(string $id, ?string $bidangId)
    {
        return SopInternal::with('lampirans')
            ->where('id', $id)
            ->where('bidang_id', $bidangId)
            ->firstOrFail();
    }

    protected function renderView(string $view, array $data)
    {
        return view($view, $data);
    }
}
