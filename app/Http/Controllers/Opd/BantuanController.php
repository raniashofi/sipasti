<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BantuanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        // Kategori dengan jumlah artikel published
        $kategoris = Kategori::withCount([
            'knowledgeBase as artikel_count' => fn($q) =>
                $q->where('status_publikasi', 'published'),
        ])->get();

        // Artikel paling sering dibaca (top 4)
        $topArtikel = KnowledgeBase::where('status_publikasi', 'published')
            ->orderByDesc('total_views')
            ->limit(4)
            ->get();

        // Hasil pencarian global
        $hasilCari = collect();
        if ($search) {
            $hasilCari = KnowledgeBase::where('status_publikasi', 'published')
                ->where(fn($q) =>
                    $q->where('nama_artikel_sop', 'like', "%{$search}%")
                      ->orWhere('deskripsi_singkat', 'like', "%{$search}%")
                )
                ->with('kategori')
                ->limit(20)
                ->get();
        }

        return view('opd.bantuan.index', compact('kategoris', 'topArtikel', 'hasilCari', 'search'));
    }

    public function kategori(string $id, Request $request)
    {
        $kategori = Kategori::findOrFail($id);
        $search   = $request->query('search', '');

        $query = KnowledgeBase::where('kategori_id', $id)
            ->where('status_publikasi', 'published');

        if ($search) {
            $query->where(fn($q) =>
                $q->where('nama_artikel_sop', 'like', "%{$search}%")
                  ->orWhere('deskripsi_singkat', 'like', "%{$search}%")
            );
        }

        $artikels = $query->orderByDesc('total_views')->get();

        return view('opd.bantuan.kategori', compact('kategori', 'artikels', 'search'));
    }

    public function artikel(string $id)
    {
        $artikel = KnowledgeBase::where('status_publikasi', 'published')
            ->with('kategori')
            ->findOrFail($id);

        // Increment view counter
        $artikel->increment('total_views');

        // Parse headings dari isi_konten untuk daftar isi
        $toc = [];
        preg_match_all('/<h([1-3])[^>]*>(.*?)<\/h[1-3]>/i', $artikel->isi_konten, $matches, PREG_SET_ORDER);
        foreach ($matches as $i => $m) {
            $toc[] = [
                'level' => (int)$m[1],
                'text'  => strip_tags($m[2]),
                'slug'  => 'heading-' . $i,
            ];
        }

        // Tambahkan id ke setiap heading dalam konten
        $konten = preg_replace_callback(
            '/<h([1-3])([^>]*)>(.*?)<\/h[1-3]>/i',
            function ($m) use (&$i) {
                static $idx = 0;
                $slug = 'heading-' . $idx++;
                return "<h{$m[1]}{$m[2]} id=\"{$slug}\">{$m[3]}</h{$m[1]}>";
            },
            $artikel->isi_konten
        );

        // Artikel terkait (kategori sama, bukan artikel ini)
        $terkait = KnowledgeBase::where('kategori_id', $artikel->kategori_id)
            ->where('id', '!=', $artikel->id)
            ->where('status_publikasi', 'published')
            ->limit(4)
            ->get();

        // Rating milik user yang sedang login untuk artikel ini
        $myRating = KnowledgeBaseRating::where('knowledge_base_id', $artikel->id)
            ->where('user_id', Auth::id())
            ->value('rating');

        return view('opd.bantuan.artikel', compact('artikel', 'konten', 'toc', 'terkait', 'myRating'));
    }

    /**
     * OPD memberi / mengubah rating artikel KB.
     * 1 user = 1 rating per artikel (upsert).
     */
    public function rating(Request $request, string $id)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);

        $artikel  = KnowledgeBase::where('status_publikasi', 'published')->findOrFail($id);
        $userId   = Auth::id();
        $newValue = (int) $request->input('rating');

        $existing = KnowledgeBaseRating::where('knowledge_base_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            // Update: hitung ulang rata-rata dengan mengganti nilai lama
            $oldValue  = $existing->rating;
            $newRating = round(
                (($artikel->rating ?? 0) * $artikel->rating_count - $oldValue + $newValue)
                / $artikel->rating_count,
                1
            );
            $existing->update(['rating' => $newValue]);

            $artikel->update(['rating' => $newRating]);
        } else {
            // Insert baru
            KnowledgeBaseRating::create([
                'knowledge_base_id' => $id,
                'user_id'           => $userId,
                'rating'            => $newValue,
            ]);

            $newCount  = $artikel->rating_count + 1;
            $newRating = round(
                (($artikel->rating ?? 0) * $artikel->rating_count + $newValue) / $newCount,
                1
            );

            $artikel->update([
                'rating'       => $newRating,
                'rating_count' => $newCount,
            ]);
        }

        return response()->json([
            'rating'       => $artikel->fresh()->rating,
            'rating_count' => $artikel->fresh()->rating_count,
            'my_rating'    => $newValue,
        ]);
    }
}
