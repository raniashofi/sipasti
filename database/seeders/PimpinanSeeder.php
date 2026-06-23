<?php

namespace Database\Seeders;

use App\Support\IdGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PimpinanSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pimpinan')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('pimpinan')->insert([
            'id'           => IdGenerator::make('USR-PIM'),
            'user_id'      => DB::table('users')->where('email', 'pimpinan@padang.go.id')->value('id'),
            'nama_lengkap' => 'Pimpinan Dinas Kominfo',
        ]);
    }
}
