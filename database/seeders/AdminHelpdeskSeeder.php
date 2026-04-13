<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminHelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('admin_helpdesk')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('admin_helpdesk')->insert([
            // Bidang E-Government (BIDANG-001) — 2 akun
            [
                'id'           => 'HD-EGV-001',
                'user_id'      => 'USR-HD-EGV-001',
                'bidang_id'    => 'BIDANG-001',
                'nama_lengkap' => 'Admin Helpdesk E-Gov 1',
            ],
            [
                'id'           => 'HD-EGV-002',
                'user_id'      => 'USR-HD-EGV-002',
                'bidang_id'    => 'BIDANG-001',
                'nama_lengkap' => 'Admin Helpdesk E-Gov 2',
            ],

            // Bidang Infrastruktur Teknologi Informasi (BIDANG-002) — 2 akun
            [
                'id'           => 'HD-ITI-001',
                'user_id'      => 'USR-HD-ITI-001',
                'bidang_id'    => 'BIDANG-002',
                'nama_lengkap' => 'Admin Helpdesk Infrastruktur 1',
            ],
            [
                'id'           => 'HD-ITI-002',
                'user_id'      => 'USR-HD-ITI-002',
                'bidang_id'    => 'BIDANG-002',
                'nama_lengkap' => 'Admin Helpdesk Infrastruktur 2',
            ],

            // Bidang Statistik & Persandian (BIDANG-003) — 2 akun
            [
                'id'           => 'HD-SPS-001',
                'user_id'      => 'USR-HD-SPS-001',
                'bidang_id'    => 'BIDANG-003',
                'nama_lengkap' => 'Admin Helpdesk Statistik 1',
            ],
            [
                'id'           => 'HD-SPS-002',
                'user_id'      => 'USR-HD-SPS-002',
                'bidang_id'    => 'BIDANG-003',
                'nama_lengkap' => 'Admin Helpdesk Statistik 2',
            ],
        ]);
    }
}
