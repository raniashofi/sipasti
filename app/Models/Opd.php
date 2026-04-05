<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $user_id
 * @property string $kode_opd
 * @property string $nama_opd
 * @property string|null $kdunit
 * @property string|null $parent_id
 * @property bool|null $is_bagian
 * @property string|null $nama_lengkap
 * @property string|null $bidang_id
 * @property string|null $status_teknisi
 */
class Opd extends Model
{
    protected $table = 'opd';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id','user_id','kode_opd','nama_opd','kdunit','parent_id',
        'is_bagian','nama_lengkap','bidang_id','status_teknisi'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tiket()
    {
        return $this->hasMany(Tiket::class);
    }
}
