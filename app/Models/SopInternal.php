<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SopInternal extends Model
{
    use HasPrefixedId, SoftDeletes;

    protected $table = 'sop_internal';
    public $incrementing = false;
    protected $keyType = 'string';
    protected string $idPrefix = 'SOP';

    protected $fillable = [
        'id', 'bidang_id', 'judul', 'deskripsi_singkat',
        'isi_konten', 'status_publikasi', 'header_image',
        'total_views',
    ];

    protected $dates = ['deleted_at'];

    public function getNamaArtikelSopAttribute(): ?string
    {
        return $this->judul;
    }

    public function getVisibilitasAksesAttribute(): string
    {
        return 'internal';
    }

    public function setNamaArtikelSopAttribute(?string $value): void
    {
        $this->attributes['judul'] = $value;
    }

    public function kategoriArtikel()
    {
        return $this->belongsTo(KategoriArtikel::class, 'kategori_artikel_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'sop_internal_tag', 'sop_internal_id', 'tag_id');
    }

    public function lampirans()
    {
        return $this->hasMany(LampiranArtikel::class, 'sop_internal_id');
    }

    public function nodes()
    {
        return $this->hasMany(NodeDiagnosis::class, 'sop_internal_id');
    }
}
