<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminHelpdeskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('admin_helpdesk')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('admin_helpdesk')->insert([
            'id'           => 'HD-001',
            'user_id'      => 'USR-ADMIN-HELPDESK', // Sesuai dengan UserSeeder
            'bidang_id'    => null, // Biarkan null jika data bidang belum ada
            'nama_lengkap' => 'Admin Helpdesk Utama',
        ]);
    }
}
