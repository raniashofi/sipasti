<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Http\Controllers\Controller;
use App\Models\AdminHelpdesk;
use App\Models\KnowledgeBase;
use App\Models\KategoriArtikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PustakaController extends Controller
{
    public function index(Request $request)
    {
        $admin = AdminHelpdesk::with('bidang')
            ->where('user_id', Auth::id())
            ->first();

        $search         = $request->query('search', '');
        $kategoriFilter = $request->query('kategori_id', '');
        $statusFilter   = $request->query('status', '');

        $query = KnowledgeBase::with('kategoriArtikel', 'tags')
            ->where('visibilitas_akses', 'internal')
            ->where('bidang_id', $admin?->bidang_id);

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

        return view('admin_helpdesk.pustaka.index', compact(
            'articles', 'kategoris', 'admin',
            'search', 'kategoriFilter', 'statusFilter'
        ));
    }

    public function show($id)
    {
        $admin = AdminHelpdesk::with('bidang')
            ->where('user_id', Auth::id())
            ->first();

        $article = KnowledgeBase::with('kategoriArtikel', 'tags')
            ->where('id', $id)
            ->where('visibilitas_akses', 'internal')
            ->where('bidang_id', $admin?->bidang_id)
            ->firstOrFail();

        return view('admin_helpdesk.pustaka.show', compact('article', 'admin'));
    }
}
