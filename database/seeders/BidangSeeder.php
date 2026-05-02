<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidangSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('bidang')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('bidang')->insert([
            ['id' => 'BIDANG-001', 'nama_bidang' => 'e_government'],
            ['id' => 'BIDANG-002', 'nama_bidang' => 'infrastruktur_teknologi_informasi'],
            ['id' => 'BIDANG-003', 'nama_bidang' => 'statistik_persandian'],
        ]);
    }
}
