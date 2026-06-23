<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedId;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id
 * @property string      $user_id
 * @property string      $bidang_id
 * @property string|null $nama_lengkap
 */
class AdminHelpdesk extends Model
{
    use HasPrefixedId;

    protected $table = 'admin_helpdesk';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected string $idPrefix = 'USR-HD';

    protected $fillable = ['id','user_id','bidang_id','nama_lengkap'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tiket()
    {
        return $this->hasMany(Tiket::class, 'admin_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id', 'id');
    }
}
