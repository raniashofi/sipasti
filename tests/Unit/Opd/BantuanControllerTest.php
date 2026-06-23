<?php

namespace Tests\Unit\Opd;

use App\Http\Controllers\Opd\BantuanController;
use Illuminate\Http\Request;
use Tests\TestCase;

class BantuanControllerTest extends TestCase
{

    public function testIndexTolakNonOpd()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']));
        $this->assertTrue(
            in_array($this->get('/opd/bantuan')->status(), [302, 403]),
            'index() — role non-opd ditolak akses bantuan'
        );
    }
    public function testIndexDaftarKategoriDanPencarian(): void
    {
        $controller = new FakeBantuanController();
        $controller->mockCategories = collect(['Jaringan', 'Aplikasi']);
        $controller->mockTopArticles = collect(['Artikel Populer']);
        $controller->mockSearchResults = collect(['Hasil Cari']);

        $result = $controller->index(Request::create('/opd/bantuan', 'GET', [
            'search' => 'internet',
        ]));

        $this->assertSame('opd.bantuan.index', $result['view']);
        $this->assertSame('internet', $result['data']['search']);
        $this->assertSame(['Jaringan', 'Aplikasi'], $result['data']['kategoris']->all());
        $this->assertSame(['Artikel Populer'], $result['data']['topArtikel']->all());
        $this->assertSame(['Hasil Cari'], $result['data']['hasilCari']->all());
        $this->assertSame('internet', $controller->searchedKeyword);
    }

    public function testIndexTanpaPencarian(): void
    {
        $controller = new FakeBantuanController();

        $result = $controller->index(Request::create('/opd/bantuan'));

        $this->assertSame('', $result['data']['search']);
        $this->assertTrue($result['data']['hasilCari']->isEmpty());
        $this->assertNull($controller->searchedKeyword);
    }

    public function testKategoriDaftarArtikel(): void
    {
        $controller = new FakeBantuanController();
        $controller->mockCategory = (object) ['id' => 'KAT-001', 'nama_kategori' => 'Jaringan'];
        $controller->mockPaginatedArticles = collect(['LAN', 'Internet']);

        $result = $controller->kategori('KAT-001', Request::create('/opd/bantuan/kategori/KAT-001', 'GET', [
            'search' => 'lan',
        ]));

        $this->assertSame('opd.bantuan.kategori', $result['view']);
        $this->assertSame('KAT-001', $controller->requestedCategoryId);
        $this->assertSame('lan', $controller->requestedCategorySearch);
        $this->assertSame($controller->mockCategory, $result['data']['kategori']);
        $this->assertSame(['LAN', 'Internet'], $result['data']['artikels']->all());
    }

    public function testArtikelTocDanHeading(): void
    {
        $controller = new FakeBantuanController();
        $controller->mockArticle = (object) [
            'id' => 'ART-001',
            'kategori_artikel_id' => 'KAT-001',
            'isi_konten' => '<h1>Judul Utama</h1><p>Isi</p><h2>Langkah Awal</h2>',
        ];
        $controller->mockRelatedArticles = collect(['Artikel Terkait']);
        $controller->mockCurrentUserRating = 4;

        $result = $controller->artikel('ART-001');

        $this->assertSame('opd.bantuan.artikel', $result['view']);
        $this->assertSame('ART-001', $controller->requestedArticleId);
        $this->assertTrue($controller->articleViewsIncremented);
        $this->assertSame([
            ['level' => 1, 'text' => 'Judul Utama', 'slug' => 'heading-0'],
            ['level' => 2, 'text' => 'Langkah Awal', 'slug' => 'heading-1'],
        ], $result['data']['toc']);
        $this->assertStringContainsString('id="heading-0"', $result['data']['konten']);
        $this->assertStringContainsString('id="heading-1"', $result['data']['konten']);
        $this->assertSame(['Artikel Terkait'], $result['data']['terkait']->all());
        $this->assertSame(4, $result['data']['myRating']);
    }

    public function testRatingBaru(): void
    {
        $controller = new FakeBantuanController();
        $controller->mockUserId = 'USR-001';
        $controller->mockArticleForRating = new FakeArticle(rating: 4.0, rating_count: 2);

        $result = $controller->rating(Request::create('/opd/bantuan/artikel/ART-001/rating', 'POST', [
            'rating' => 5,
        ]), 'ART-001');

        $this->assertSame([
            'artikel_opd_id' => 'ART-001',
            'user_id' => 'USR-001',
            'rating' => 5,
        ], $controller->createdRating);
        $this->assertSame(['rating' => 4.3, 'rating_count' => 3], $controller->updatedArticleAttributes);
        $this->assertSame(['rating' => 4.3, 'rating_count' => 3, 'my_rating' => 5], $result);
    }

    public function testRatingUpdate(): void
    {
        $controller = new FakeBantuanController();
        $controller->mockUserId = 'USR-001';
        $controller->mockArticleForRating = new FakeArticle(rating: 4.0, rating_count: 2);
        $controller->mockExistingRating = new FakeRating(3);

        $result = $controller->rating(Request::create('/opd/bantuan/artikel/ART-001/rating', 'POST', [
            'rating' => 5,
        ]), 'ART-001');

        $this->assertSame(['rating' => 5], $controller->mockExistingRating->updatedAttributes);
        $this->assertSame(['rating' => 5.0], $controller->updatedArticleAttributes);
        $this->assertSame(['rating' => 5.0, 'rating_count' => 2, 'my_rating' => 5], $result);
    }
}

class FakeBantuanController extends BantuanController
{
    public $mockCategories;
    public $mockTopArticles;
    public $mockSearchResults;
    public $mockCategory;
    public $mockPaginatedArticles;
    public $mockArticle;
    public $mockRelatedArticles;
    public $mockCurrentUserRating = null;
    public $mockArticleForRating;
    public $mockExistingRating = null;
    public ?string $searchedKeyword = null;
    public ?string $requestedCategoryId = null;
    public ?string $requestedCategorySearch = null;
    public ?string $requestedArticleId = null;
    public bool $articleViewsIncremented = false;
    public string $mockUserId = 'USR-001';
    public ?array $createdRating = null;
    public ?array $updatedArticleAttributes = null;

    protected function publishedCategories()
    {
        return $this->mockCategories ?? collect();
    }

    protected function topPublishedArticles()
    {
        return $this->mockTopArticles ?? collect();
    }

    protected function searchPublishedArticles(string $search)
    {
        $this->searchedKeyword = $search;
        return $this->mockSearchResults ?? collect();
    }

    protected function findCategoryOrFail(string $id)
    {
        $this->requestedCategoryId = $id;
        return $this->mockCategory;
    }

    protected function paginatedPublishedArticlesByCategory(string $id, string $search)
    {
        $this->requestedCategoryId = $id;
        $this->requestedCategorySearch = $search;
        return $this->mockPaginatedArticles ?? collect();
    }

    protected function findPublishedArticleOrFail(string $id)
    {
        $this->requestedArticleId = $id;
        return $this->mockArticle;
    }

    protected function incrementArticleViews($artikel): void
    {
        $this->articleViewsIncremented = true;
    }

    protected function relatedPublishedArticles($artikel)
    {
        return $this->mockRelatedArticles ?? collect();
    }

    protected function currentUserRating(string $articleId)
    {
        return $this->mockCurrentUserRating;
    }

    protected function findPublishedArticleForRatingOrFail(string $id)
    {
        return $this->mockArticleForRating;
    }

    protected function authenticatedUserId()
    {
        return $this->mockUserId;
    }

    protected function findExistingRating(string $articleId, string $userId)
    {
        return $this->mockExistingRating;
    }

    protected function updateExistingRating($existing, int $newValue): void
    {
        $existing->update(['rating' => $newValue]);
    }

    protected function createArticleRating(array $attributes): void
    {
        $this->createdRating = $attributes;
    }

    protected function updateArticleRating($artikel, array $attributes): void
    {
        $this->updatedArticleAttributes = $attributes;
        foreach ($attributes as $key => $value) {
            $artikel->{$key} = $value;
        }
    }

    protected function freshArticle($artikel)
    {
        return $artikel;
    }

    protected function renderView(string $view, array $data)
    {
        return ['view' => $view, 'data' => $data];
    }

    protected function jsonResponse(array $data)
    {
        return $data;
    }
}

class FakeArticle
{
    public function __construct(public float $rating, public int $rating_count)
    {
    }
}

class FakeRating
{
    public ?array $updatedAttributes = null;

    public function __construct(public int $rating)
    {
    }

    public function update(array $attributes): void
    {
        $this->updatedAttributes = $attributes;
        $this->rating = $attributes['rating'];
    }
}
