<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $user_id
 * @property string $role_pelaku
 * @property string $jenis_aktivitas
 * @property string|null $detail_tindakan
 * @property string|null $ip_address
 * @property string|null $session_id
 * @property \Carbon\Carbon|null $waktu_eksekusi
 * @property string|null $nama_tabel
 * @property string|null $id_record
 * @property array|null $data_before
 * @property array|null $data_after
 */
class ActivityLog extends Model
{
    protected $table = 'activity_log';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role_pelaku',
        'jenis_aktivitas',
        'detail_tindakan',
        'ip_address',
        'session_id',
        'waktu_eksekusi',
        'nama_tabel',
        'id_record',
        'data_before',
        'data_after',
    ];

    protected $casts = [
        'waktu_eksekusi' => 'datetime',
        'data_before'    => 'array',
        'data_after'     => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
