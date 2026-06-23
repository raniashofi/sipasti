<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $nama_tag
 * @property string $slug
 */
class Tag extends Model
{
    use HasPrefixedId;

    protected $table = 'tag';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected string $idPrefix = 'TAG';

    protected $fillable = ['id', 'nama_tag', 'slug'];

    public function articles()
    {
        return $this->belongsToMany(ArtikelOpd::class, 'artikel_opd_tag', 'tag_id', 'artikel_opd_id');
    }

    public function internalArticles()
    {
        return $this->belongsToMany(SopInternal::class, 'sop_internal_tag', 'tag_id', 'sop_internal_id');
    }
}
