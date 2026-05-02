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

        $timTeknis = [
            // Bidang E-Government (BIDANG-001)
            [
                'id'             => 'TKN-EGV-001',
                'user_id'        => 'USR-TIM-EGV-001',
                'bidang_id'      => 'BIDANG-001',
                'nama_lengkap'   => 'Tim Teknis E-Government 1',
                'status_teknisi' => 'online',
            ],
            [
                'id'             => 'TKN-EGV-002',
                'user_id'        => 'USR-TIM-EGV-002',
                'bidang_id'      => 'BIDANG-001',
                'nama_lengkap'   => 'Tim Teknis E-Government 2',
                'status_teknisi' => 'online',
            ],
            // Bidang Infrastruktur Teknologi Informasi (BIDANG-002)
            [
                'id'             => 'TKN-ITI-001',
                'user_id'        => 'USR-TIM-ITI-001',
                'bidang_id'      => 'BIDANG-002',
                'nama_lengkap'   => 'Tim Teknis Infrastruktur IT 1',
                'status_teknisi' => 'online',
            ],
            [
                'id'             => 'TKN-ITI-002',
                'user_id'        => 'USR-TIM-ITI-002',
                'bidang_id'      => 'BIDANG-002',
                'nama_lengkap'   => 'Tim Teknis Infrastruktur IT 2',
                'status_teknisi' => 'online',
            ],
            // Bidang Statistik & Persandian (BIDANG-003)
            [
                'id'             => 'TKN-SPS-001',
                'user_id'        => 'USR-TIM-SPS-001',
                'bidang_id'      => 'BIDANG-003',
                'nama_lengkap'   => 'Tim Teknis Statistik & Persandian 1',
                'status_teknisi' => 'online',
            ],
            [
                'id'             => 'TKN-SPS-002',
                'user_id'        => 'USR-TIM-SPS-002',
                'bidang_id'      => 'BIDANG-003',
                'nama_lengkap'   => 'Tim Teknis Statistik & Persandian 2',
                'status_teknisi' => 'online',
            ],
        ];

        DB::table('tim_teknis')->insert($timTeknis);
    }
}
