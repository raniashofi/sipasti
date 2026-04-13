<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseRating extends Model
{
    protected $table = 'knowledge_base_rating';
    public $timestamps = false;

    protected $fillable = ['knowledge_base_id', 'user_id', 'rating'];

    public function knowledgeBase()
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
