<?php

namespace Database\Seeders;

use App\Support\IdGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminHelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('admin_helpdesk')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bidangEgov = DB::table('bidang')->where('nama_bidang', 'E-Government')->value('id');
        $bidangInfra = DB::table('bidang')->where('nama_bidang', 'Infrastruktur TI')->value('id');
        $bidangStatistik = DB::table('bidang')->where('nama_bidang', 'Statistik & Persandian')->value('id');

        DB::table('admin_helpdesk')->insert([
            [
                'id'           => IdGenerator::make('USR-HD'),
                'user_id'      => DB::table('users')->where('email', 'helpdesk.egov1@padang.go.id')->value('id'),
                'bidang_id'    => $bidangEgov,
                'nama_lengkap' => 'Admin Helpdesk E-Gov 1',
            ],
            [
                'id'           => IdGenerator::make('USR-HD'),
                'user_id'      => DB::table('users')->where('email', 'helpdesk.egov2@padang.go.id')->value('id'),
                'bidang_id'    => $bidangEgov,
                'nama_lengkap' => 'Admin Helpdesk E-Gov 2',
            ],
            [
                'id'           => IdGenerator::make('USR-HD'),
                'user_id'      => DB::table('users')->where('email', 'helpdesk.infra1@padang.go.id')->value('id'),
                'bidang_id'    => $bidangInfra,
                'nama_lengkap' => 'Admin Helpdesk Infrastruktur 1',
            ],
            [
                'id'           => IdGenerator::make('USR-HD'),
                'user_id'      => DB::table('users')->where('email', 'helpdesk.infra2@padang.go.id')->value('id'),
                'bidang_id'    => $bidangInfra,
                'nama_lengkap' => 'Admin Helpdesk Infrastruktur 2',
            ],
            [
                'id'           => IdGenerator::make('USR-HD'),
                'user_id'      => DB::table('users')->where('email', 'helpdesk.statistik1@padang.go.id')->value('id'),
                'bidang_id'    => $bidangStatistik,
                'nama_lengkap' => 'Admin Helpdesk Statistik 1',
            ],
            [
                'id'           => IdGenerator::make('USR-HD'),
                'user_id'      => DB::table('users')->where('email', 'helpdesk.statistik2@padang.go.id')->value('id'),
                'bidang_id'    => $bidangStatistik,
                'nama_lengkap' => 'Admin Helpdesk Statistik 2',
            ],
        ]);
    }
}
