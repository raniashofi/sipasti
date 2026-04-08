<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pimpinan extends Model
{
    use HasFactory;

    // Mendefinisikan nama tabel secara eksplisit
    protected $table = 'pimpinan';

    // Memberi tahu Eloquent bahwa primary key adalah string dan bukan auto-increment
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom-kolom yang diizinkan untuk diisi secara massal (mass assignment)
    protected $fillable = [
        'id',
        'user_id',
        'nama_lengkap',
    ];

    /**
     * Relasi ke model User
     * Setiap data pimpinan dimiliki oleh satu user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
