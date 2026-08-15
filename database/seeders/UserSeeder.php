<?php

namespace Database\Seeders;

use App\Support\IdGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('opd')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $users = [];

        // Super Admin
        $users[] = [
            'id'       => IdGenerator::make('USR-SUPER'),
            'email'    => 'superadmin@padang.go.id',
            'password' => Hash::make('superadmin123'),
            'gambar'   => null,
            'role'     => 'super_admin',
        ];

        // Admin Helpdesk — 2 akun per bidang (3 bidang = 6 akun)
        $helpdeskAccounts = [
            // Bidang E-Government
            ['email' => 'helpdesk.egov1@padang.go.id',      'password' => 'egov1123'],
            ['email' => 'helpdesk.egov2@padang.go.id',      'password' => 'egov2123'],
            // Bidang Infrastruktur Teknologi Informasi
            ['email' => 'helpdesk.infra1@padang.go.id',     'password' => 'infra1123'],
            ['email' => 'helpdesk.infra2@padang.go.id',     'password' => 'infra2123'],
            // Bidang Statistik & Persandian
            ['email' => 'helpdesk.statistik1@padang.go.id', 'password' => 'statistik1123'],
            ['email' => 'helpdesk.statistik2@padang.go.id', 'password' => 'statistik2123'],
        ];

        foreach ($helpdeskAccounts as $hd) {
            $this->command->line("  email: {$hd['email']} | password: {$hd['password']}");
            $users[] = [
                'id'       => IdGenerator::make('USR-HD'),
                'email'    => $hd['email'],
                'password' => Hash::make($hd['password']),
                'gambar'   => null,
                'role'     => 'admin_helpdesk',
            ];
        }

        // Tim Teknis — 2 per bidang (3 bidang = 6 tim teknis)
        $timTeknis = [
            // Bidang E-Government
            ['email' => 'timteknis.egov1@padang.go.id',      'password' => 'timegov1123'],
            ['email' => 'timteknis.egov2@padang.go.id',      'password' => 'timegov2123'],
            // Bidang Infrastruktur Teknologi Informasi
            ['email' => 'timteknis.infra1@padang.go.id',     'password' => 'timeinfra1123'],
            ['email' => 'timteknis.infra2@padang.go.id',     'password' => 'timeinfra2123'],
            // Bidang Statistik & Persandian
            ['email' => 'timteknis.persandian1@padang.go.id', 'password' => 'timepersandian1123'],
            ['email' => 'timteknis.persandian2@padang.go.id', 'password' => 'timepersandian2123'],
        ];

        foreach ($timTeknis as $tt) {
            $this->command->line("  email: {$tt['email']} | password: {$tt['password']}");
            $users[] = [
                'id'       => IdGenerator::make('USR-TIM'),
                'email'    => $tt['email'],
                'password' => Hash::make($tt['password']),
                'gambar'   => null,
                'role'     => 'tim_teknis',
            ];
        }

        // Pimpinan
        $users[] = [
            'id'       => IdGenerator::make('USR-PIM'),
            'email'    => 'pimpinan@padang.go.id',
            'password' => Hash::make('pimpinan123'),
            'gambar'   => null,
            'role'     => 'pimpinan',
        ];

        // password default = slug + 123, kecuali akun uji yang sengaja diset khusus.
        $opds = [
            ['slug' => 'contoh1',          'email' => 'contoh1@gmail.com',          'password' => 'contoh1'],
            ['slug' => 'disdikbud',         'email' => 'disdikbud@padang.go.id'],
            ['slug' => 'dinkes',             'email' => 'dinkes@padang.go.id'],
            ['slug' => 'rsud-rasidin',       'email' => 'rsud-rasidin@padang.go.id'],
            ['slug' => 'dpupr',              'email' => 'dpupr@padang.go.id'],
            ['slug' => 'disperkim',          'email' => 'disperkim@padang.go.id'],
            ['slug' => 'satpol-pp',          'email' => 'satpol-pp@padang.go.id'],
            ['slug' => 'bpbd',               'email' => 'bpbd@padang.go.id'],
            ['slug' => 'damkar',             'email' => 'damkar@padang.go.id'],
            ['slug' => 'dinsos',             'email' => 'dinsos@padang.go.id'],
            ['slug' => 'disnaker',           'email' => 'disnaker@padang.go.id'],
            ['slug' => 'dp3ap2kb',           'email' => 'dp3ap2kb@padang.go.id'],
            ['slug' => 'disperikanan',       'email' => 'disperikanan@padang.go.id'],
            ['slug' => 'dispertanahan',      'email' => 'dispertanahan@padang.go.id'],
            ['slug' => 'dlh',                'email' => 'dlh@padang.go.id'],
            ['slug' => 'disdukcapil',        'email' => 'disdukcapil@padang.go.id'],
            ['slug' => 'dishub',             'email' => 'dishub@padang.go.id'],
            ['slug' => 'diskominfo',         'email' => 'diskominfo@padang.go.id'],
            ['slug' => 'diskop-ukm',         'email' => 'diskop-ukm@padang.go.id'],
            ['slug' => 'dpmptsp',            'email' => 'dpmptsp@padang.go.id'],
            ['slug' => 'dispora',            'email' => 'dispora@padang.go.id'],
            ['slug' => 'disperpusip',        'email' => 'disperpusip@padang.go.id'],
            ['slug' => 'dispariwisata',      'email' => 'dispariwisata@padang.go.id'],
            ['slug' => 'dispertan',          'email' => 'dispertan@padang.go.id'],
            ['slug' => 'disdag',             'email' => 'disdag@padang.go.id'],
            ['slug' => 'setda',              'email' => 'setda@padang.go.id'],
            ['slug' => 'sekwan',             'email' => 'sekwan@padang.go.id'],
            ['slug' => 'bappeda',            'email' => 'bappeda@padang.go.id'],
            ['slug' => 'bpkad',              'email' => 'bpkad@padang.go.id'],
            ['slug' => 'bapenda',            'email' => 'bapenda@padang.go.id'],
            ['slug' => 'bkpsdm',             'email' => 'bkpsdm@padang.go.id'],
            ['slug' => 'inspektorat',        'email' => 'inspektorat@padang.go.id'],
            ['slug' => 'kec-padbar',         'email' => 'kec-padbar@padang.go.id'],
            ['slug' => 'kec-padtim',         'email' => 'kec-padtim@padang.go.id'],
            ['slug' => 'kec-padut',          'email' => 'kec-padut@padang.go.id'],
            ['slug' => 'kec-padsel',         'email' => 'kec-padsel@padang.go.id'],
            ['slug' => 'kec-nanggalo',       'email' => 'kec-nanggalo@padang.go.id'],
            ['slug' => 'kec-kuranji',        'email' => 'kec-kuranji@padang.go.id'],
            ['slug' => 'kec-lubeg',          'email' => 'kec-lubeg@padang.go.id'],
            ['slug' => 'kec-lubuk-kilangan', 'email' => 'kec-lubuk-kilangan@padang.go.id'],
            ['slug' => 'kec-pauh',           'email' => 'kec-pauh@padang.go.id'],
            ['slug' => 'kec-kotangah',       'email' => 'kec-kotangah@padang.go.id'],
            ['slug' => 'kec-bungus',         'email' => 'kec-bungus@padang.go.id'],
            ['slug' => 'kesbangpol',         'email' => 'kesbangpol@padang.go.id'],
            // Bagian Setda
            ['slug' => 'bag-hukum',              'email' => 'bag-hukum@padang.go.id'],
            ['slug' => 'bag-umum',               'email' => 'bag-umum@padang.go.id'],
            ['slug' => 'bag-perekonomian',        'email' => 'bag-perekonomian@padang.go.id'],
            ['slug' => 'bag-organisasi',          'email' => 'bag-organisasi@padang.go.id'],
            ['slug' => 'bag-protokol',            'email' => 'bag-protokol@padang.go.id'],
            ['slug' => 'bag-keuangan',            'email' => 'bag-keuangan@padang.go.id'],
            ['slug' => 'bag-pbj',                 'email' => 'bag-pbj@padang.go.id'],
            ['slug' => 'bag-perencanaan',         'email' => 'bag-perencanaan@padang.go.id'],
            ['slug' => 'bag-kerjasama',           'email' => 'bag-kerjasama@padang.go.id'],
            ['slug' => 'bag-adm-pembangunan',     'email' => 'bag-adm-pembangunan@padang.go.id'],
        ];

        foreach ($opds as $opd) {
            $slug     = $opd['slug'];
            $password = $opd['password'] ?? ($slug . '123');

            $this->command->line("  email: {$opd['email']} | password: {$password}");

            $users[] = [
                'id'       => IdGenerator::make('USR-OPD'),
                'email'    => $opd['email'],
                'password' => Hash::make($password),
                'gambar'   => null,
                'role'     => 'opd',
            ];
        }

        DB::table('users')->insert($users);
    }
}
