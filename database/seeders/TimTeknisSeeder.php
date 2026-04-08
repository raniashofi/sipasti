<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimTeknisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tim_teknis')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('tim_teknis')->insert([
            'id'             => 'TKN-001',
            'user_id'        => 'USR-TIM-TEKNIS', // Sesuai dengan UserSeeder
            'bidang_id'      => null, // Biarkan null jika data bidang belum ada
            'nama_lengkap'   => 'Tim Teknis Kominfo',
            'status_teknisi' => 'offline',
        ]);
    }
}
