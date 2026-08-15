<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtikelOpd extends Model
{
    use HasPrefixedId, SoftDeletes;

    protected $table = 'artikel_opd';
    public $incrementing = false;
    protected $keyType = 'string';
    protected string $idPrefix = 'ART';

    protected $fillable = [
        'id', 'kategori_artikel_id', 'judul', 'deskripsi_singkat',
        'isi_konten', 'status_publikasi', 'header_image',
        'total_views', 'rating', 'rating_count',
    ];

    protected $dates = ['deleted_at'];

    public function getNamaArtikelSopAttribute(): ?string
    {
        return $this->judul;
    }

    public function getVisibilitasAksesAttribute(): string
    {
        return 'opd';
    }

    public function setNamaArtikelSopAttribute(?string $value): void
    {
        $this->attributes['judul'] = $value;
    }

    public function kategoriArtikel()
    {
        return $this->belongsTo(KategoriArtikel::class, 'kategori_artikel_id');
    }

    public function kategori()
    {
        return $this->kategoriArtikel();
    }


    public function lampirans()
    {
        return $this->hasMany(LampiranArtikel::class, 'artikel_opd_id');
    }

    public function nodes()
    {
        return $this->hasMany(NodeDiagnosis::class, 'artikel_opd_id');
    }

    public function ratings()
    {
        return $this->hasMany(ArtikelOpdRating::class, 'artikel_opd_id');
    }
}
