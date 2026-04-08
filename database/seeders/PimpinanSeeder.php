<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PimpinanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pimpinan')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('pimpinan')->insert([
            'id'           => 'PMP-001',
            'user_id'      => 'USR-PIMPINAN', // Sesuai dengan UserSeeder
            'nama_lengkap' => 'Pimpinan Dinas Kominfo',
        ]);
    }
}
