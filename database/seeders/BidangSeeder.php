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
            ['id' => 'BIDANG-001', 'nama_bidang' => 'E-Government'],
            ['id' => 'BIDANG-002', 'nama_bidang' => 'Infrastruktur TI'],
            ['id' => 'BIDANG-003', 'nama_bidang' => 'Statistik & Persandian'],
        ]);
    }
}
