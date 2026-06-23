<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

class ArtikelOpdRating extends Model
{
    use HasPrefixedId;

    protected $table = 'artikel_opd_rating';
    public $incrementing = false;
    protected $keyType = 'string';
    protected string $idPrefix = 'RAT';

    protected $fillable = ['id', 'artikel_opd_id', 'user_id', 'rating'];

    public function artikel()
    {
        return $this->belongsTo(ArtikelOpd::class, 'artikel_opd_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
