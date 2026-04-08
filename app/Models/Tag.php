<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $nama_tag
 * @property string $slug
 */
class Tag extends Model
{
    protected $table = 'tag';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = ['id', 'nama_tag', 'slug'];

    /**
     * Relationship: Knowledge Base Articles (Many-to-Many)
     */
    public function articles()
    {
        return $this->belongsToMany(KnowledgeBase::class, 'knowledge_base_tag');
    }
}
