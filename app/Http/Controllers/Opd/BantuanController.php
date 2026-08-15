<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\ArtikelOpd;
use App\Models\ArtikelOpdRating;
use App\Models\KategoriArtikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BantuanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        $kategoris = $this->publishedCategories();
        $topArtikel = $this->topPublishedArticles();
        $hasilCari = $search ? $this->searchPublishedArticles($search) : collect();

        return $this->renderView('opd.bantuan.index', compact('kategoris', 'topArtikel', 'hasilCari', 'search'));
    }

    public function kategori(string $id, Request $request)
    {
        $kategori = $this->findCategoryOrFail($id);
        $search   = $request->query('search', '');

        $artikels = $this->paginatedPublishedArticlesByCategory($id, $search);

        return $this->renderView('opd.bantuan.kategori', compact('kategori', 'artikels', 'search'));
    }

    public function artikel(string $id)
    {
        $artikel = $this->findPublishedArticleOrFail($id);

        $this->incrementArticleViews($artikel);

        ['toc' => $toc, 'konten' => $konten] = $this->buildTableOfContents($artikel->isi_konten);
        $terkait = $this->relatedPublishedArticles($artikel);
        $myRating = $this->currentUserRating($artikel->id);

        return $this->renderView('opd.bantuan.artikel', compact('artikel', 'konten', 'toc', 'terkait', 'myRating'));
    }

    public function rating(Request $request, string $id)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);

        $artikel  = $this->findPublishedArticleForRatingOrFail($id);
        $userId   = $this->authenticatedUserId();
        $newValue = (int) $request->input('rating');

        $existing = $this->findExistingRating($id, $userId);

        if ($existing) {
            $oldValue  = $existing->rating;
            $newRating = round(
                (($artikel->rating ?? 0) * $artikel->rating_count - $oldValue + $newValue)
                / $artikel->rating_count,
                1
            );
            $this->updateExistingRating($existing, $newValue);
            $this->updateArticleRating($artikel, ['rating' => $newRating]);
        } else {
            $this->createArticleRating([
                'artikel_opd_id'    => $id,
                'user_id'           => $userId,
                'rating'            => $newValue,
            ]);

            $newCount  = $artikel->rating_count + 1;
            $newRating = round(
                (($artikel->rating ?? 0) * $artikel->rating_count + $newValue) / $newCount,
                1
            );

            $this->updateArticleRating($artikel, [
                'rating'       => $newRating,
                'rating_count' => $newCount,
            ]);
        }

        $freshArticle = $this->freshArticle($artikel);

        return $this->jsonResponse([
            'rating'       => $freshArticle->rating,
            'rating_count' => $freshArticle->rating_count,
            'my_rating'    => $newValue,
        ]);
    }

    protected function publishedCategories()
    {
        return KategoriArtikel::withCount([
            'knowledgeBases as artikel_count' => fn($q) =>
                $q->where('status_publikasi', 'published'),
        ])
        ->whereHas('knowledgeBases', fn($q) => $q->where('status_publikasi', 'published'))
        ->orderBy('nama_kategori')
        ->get();
    }

    protected function topPublishedArticles()
    {
        return ArtikelOpd::where('status_publikasi', 'published')
            ->orderByDesc('total_views')
            ->limit(4)
            ->get();
    }

    protected function searchPublishedArticles(string $search)
    {
        return ArtikelOpd::where('status_publikasi', 'published')
            ->where(fn($q) =>
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi_singkat', 'like', "%{$search}%")
            )
            ->with('kategoriArtikel')
            ->limit(20)
            ->get();
    }

    protected function findCategoryOrFail(string $id)
    {
        return KategoriArtikel::findOrFail($id);
    }

    protected function paginatedPublishedArticlesByCategory(string $id, string $search)
    {
        $query = ArtikelOpd::where('kategori_artikel_id', $id)
            ->where('status_publikasi', 'published');

        if ($search) {
            $query->where(fn($q) =>
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi_singkat', 'like', "%{$search}%")
            );
        }

        return $query->orderByDesc('total_views')->paginate(10)->appends(['search' => $search]);
    }

    protected function findPublishedArticleOrFail(string $id)
    {
        return ArtikelOpd::where('status_publikasi', 'published')
            ->with('kategoriArtikel', 'lampirans')
            ->findOrFail($id);
    }

    protected function incrementArticleViews($artikel): void
    {
        $artikel->increment('total_views');
    }

    protected function buildTableOfContents(?string $html): array
    {
        $html = $html ?? '';
        $toc = [];
        preg_match_all('/<h([1-3])[^>]*>(.*?)<\/h[1-3]>/i', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $i => $m) {
            $toc[] = [
                'level' => (int)$m[1],
                'text'  => strip_tags($m[2]),
                'slug'  => 'heading-' . $i,
            ];
        }

        $konten = preg_replace_callback(
            '/<h([1-3])([^>]*)>(.*?)<\/h[1-3]>/i',
            function ($m) {
                static $idx = 0;
                $slug = 'heading-' . $idx++;
                return "<h{$m[1]}{$m[2]} id=\"{$slug}\">{$m[3]}</h{$m[1]}>";
            },
            $html
        );

        return ['toc' => $toc, 'konten' => $konten];
    }

    protected function relatedPublishedArticles($artikel)
    {
        return ArtikelOpd::where('kategori_artikel_id', $artikel->kategori_artikel_id)
            ->where('id', '!=', $artikel->id)
            ->where('status_publikasi', 'published')
            ->limit(4)
            ->get();
    }

    protected function currentUserRating(string $articleId)
    {
        return ArtikelOpdRating::where('artikel_opd_id', $articleId)
            ->where('user_id', Auth::id())
            ->value('rating');
    }

    protected function findPublishedArticleForRatingOrFail(string $id)
    {
        return ArtikelOpd::where('status_publikasi', 'published')->findOrFail($id);
    }

    protected function authenticatedUserId()
    {
        return Auth::id();
    }

    protected function findExistingRating(string $articleId, string $userId)
    {
        return ArtikelOpdRating::where('artikel_opd_id', $articleId)
            ->where('user_id', $userId)
            ->first();
    }

    protected function updateExistingRating($existing, int $newValue): void
    {
        $existing->update(['rating' => $newValue]);
    }

    protected function createArticleRating(array $attributes): void
    {
        ArtikelOpdRating::create($attributes);
    }

    protected function updateArticleRating($artikel, array $attributes): void
    {
        $artikel->update($attributes);
    }

    protected function freshArticle($artikel)
    {
        return $artikel->fresh();
    }

    protected function renderView(string $view, array $data)
    {
        return view($view, $data);
    }

    protected function jsonResponse(array $data)
    {
        return response()->json($data);
    }
}
