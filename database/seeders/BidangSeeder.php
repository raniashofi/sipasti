<?php

namespace Database\Seeders;

use App\Support\IdGenerator;
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
            ['id' => IdGenerator::make('BDG'), 'nama_bidang' => 'E-Government', 'batas_hari_pengerjaan' => 3],
            ['id' => IdGenerator::make('BDG'), 'nama_bidang' => 'Infrastruktur TI', 'batas_hari_pengerjaan' => 5],
            ['id' => IdGenerator::make('BDG'), 'nama_bidang' => 'Statistik & Persandian', 'batas_hari_pengerjaan' => 4],
        ]);
    }
}
