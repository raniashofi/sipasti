<?php

namespace Database\Seeders;

use App\Support\IdGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimTeknisSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tim_teknis')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bidangEgov = DB::table('bidang')->where('nama_bidang', 'E-Government')->value('id');
        $bidangInfra = DB::table('bidang')->where('nama_bidang', 'Infrastruktur TI')->value('id');
        $bidangStatistik = DB::table('bidang')->where('nama_bidang', 'Statistik & Persandian')->value('id');

        DB::table('tim_teknis')->insert([
            [
                'id'             => IdGenerator::make('USR-TIM'),
                'user_id'        => DB::table('users')->where('email', 'timteknis.egov1@padang.go.id')->value('id'),
                'bidang_id'      => $bidangEgov,
                'nama_lengkap'   => 'Tim Teknis E-Government 1',
                'status_teknisi' => 'online',
            ],
            [
                'id'             => IdGenerator::make('USR-TIM'),
                'user_id'        => DB::table('users')->where('email', 'timteknis.egov2@padang.go.id')->value('id'),
                'bidang_id'      => $bidangEgov,
                'nama_lengkap'   => 'Tim Teknis E-Government 2',
                'status_teknisi' => 'online',
            ],
            [
                'id'             => IdGenerator::make('USR-TIM'),
                'user_id'        => DB::table('users')->where('email', 'timteknis.infra1@padang.go.id')->value('id'),
                'bidang_id'      => $bidangInfra,
                'nama_lengkap'   => 'Tim Teknis Infrastruktur IT 1',
                'status_teknisi' => 'online',
            ],
            [
                'id'             => IdGenerator::make('USR-TIM'),
                'user_id'        => DB::table('users')->where('email', 'timteknis.infra2@padang.go.id')->value('id'),
                'bidang_id'      => $bidangInfra,
                'nama_lengkap'   => 'Tim Teknis Infrastruktur IT 2',
                'status_teknisi' => 'online',
            ],
            [
                'id'             => IdGenerator::make('USR-TIM'),
                'user_id'        => DB::table('users')->where('email', 'timteknis.persandian1@padang.go.id')->value('id'),
                'bidang_id'      => $bidangStatistik,
                'nama_lengkap'   => 'Tim Teknis Statistik & Persandian 1',
                'status_teknisi' => 'online',
            ],
            [
                'id'             => IdGenerator::make('USR-TIM'),
                'user_id'        => DB::table('users')->where('email', 'timteknis.persandian2@padang.go.id')->value('id'),
                'bidang_id'      => $bidangStatistik,
                'nama_lengkap'   => 'Tim Teknis Statistik & Persandian 2',
                'status_teknisi' => 'online',
            ],
        ]);
    }
}
