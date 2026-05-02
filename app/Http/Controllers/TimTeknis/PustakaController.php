<?php

namespace App\Http\Controllers\TimTeknis;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use App\Models\KategoriArtikel;
use App\Models\TimTeknis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PustakaController extends Controller
{
    private function teknisProfile(): ?TimTeknis
    {
        return TimTeknis::with('bidang')->where('user_id', Auth::id())->first();
    }

    public function index(Request $request)
    {
        $teknis  = $this->teknisProfile();

        $search         = $request->query('search', '');
        $kategoriFilter = $request->query('kategori_id', '');
        $statusFilter   = $request->query('status', '');

        $query = KnowledgeBase::with('kategoriArtikel', 'tags')
            ->where('visibilitas_akses', 'internal');

        if ($search) {
            $query->where(fn($q) =>
                $q->where('nama_artikel_sop', 'like', "%{$search}%")
                  ->orWhereHas('tags', fn($qt) => $qt->where('nama_tag', 'like', "%{$search}%"))
            );
        }
        if ($kategoriFilter) { $query->where('kategori_artikel_id', $kategoriFilter); }
        if ($statusFilter)   { $query->where('status_publikasi', $statusFilter); }

        $articles  = $query->orderByDesc('created_at')->get();
        $kategoris = KategoriArtikel::orderBy('nama_kategori')->get();

        return view('tim_teknis.pustaka', compact(
            'articles', 'kategoris', 'teknis',
            'search', 'kategoriFilter', 'statusFilter'
        ));
    }

    public function show(string $id)
    {
        $teknis = $this->teknisProfile();

        $article = KnowledgeBase::with('kategoriArtikel', 'tags')
            ->where('id', $id)
            ->where('visibilitas_akses', 'internal')
            ->firstOrFail();

        return view('tim_teknis.pustaka-show', compact('article', 'teknis'));
    }
}
