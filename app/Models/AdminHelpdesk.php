<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminHelpdesk extends Model
{
    protected $table = 'admin_helpdesk';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id','user_id','bidang_id','nama_lengkap'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tiket()
    {
        return $this->hasMany(Tiket::class, 'admin_id');
    }
}
