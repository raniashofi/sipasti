<?php

namespace App\Http\Controllers\TimTeknis;

use App\Http\Controllers\Controller;
use App\Models\KategoriArtikel;
use App\Models\SopInternal;
use App\Models\TimTeknis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PustakaController extends Controller
{
    public function index(Request $request)
    {
        $teknis = $this->findTeknisProfile();

        $search         = $request->query('search', '');
        $kategoriFilter = $request->query('kategori_id', '');
        $statusFilter   = $request->query('status', '');

        $bidangId  = $teknis?->bidang_id;
        $articles  = $this->queryArticles($search, $statusFilter, $bidangId);
        $kategoris = $this->allKategoris();

        return $this->renderView('tim_teknis.pustaka', compact(
            'articles', 'kategoris', 'teknis',
            'search', 'kategoriFilter', 'statusFilter'
        ));
    }

    public function show(string $id)
    {
        $teknis  = $this->findTeknisProfile();
        $article = $this->findArticleOrFail($id);

        return $this->renderView('tim_teknis.pustaka-show', compact('article', 'teknis'));
    }

    // ── Protected methods ────────────────────────────────────

    protected function findTeknisProfile()
    {
        return TimTeknis::with('bidang')->where('user_id', Auth::id())->first();
    }

    protected function queryArticles(string $search, string $statusFilter, ?string $bidangId = null)
    {
        $query = SopInternal::orderByDesc('created_at');

        // Filter berdasarkan bidang teknisi yang sedang login
        if ($bidangId) {
            $query->where('bidang_id', $bidangId);
        }

        if ($search) {
            $query->where(fn($q) =>
                $q->where('judul', 'like', "%{$search}%")
            );
        }
        if ($statusFilter) { $query->where('status_publikasi', $statusFilter); }
        return $query->get();
    }

    protected function allKategoris()
    {
        return KategoriArtikel::orderBy('nama_kategori')->get();
    }

    protected function findArticleOrFail(string $id)
    {
        return SopInternal::with('lampirans')->where('id', $id)->firstOrFail();
    }

    protected function renderView(string $view, array $data)
    {
        return view($view, $data);
    }
}
