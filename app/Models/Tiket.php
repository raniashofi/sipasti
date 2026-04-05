<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tiket extends Model
{
    protected $table = 'tiket';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function admin()
    {
        return $this->belongsTo(AdminHelpdesk::class, 'admin_id');
    }

    public function statusTiket()
    {
        return $this->hasMany(StatusTiket::class);
    }

    public function latestStatus()
    {
        return $this->hasOne(StatusTiket::class)->latestOfMany('id');
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }
}
