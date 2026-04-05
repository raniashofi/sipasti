<?php

namespace Database\Seeders;

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
            'id'       => 'USR-SUPER-ADMIN',
            'email'    => 'superadmin@padang.go.id',
            'password' => Hash::make('superadmin123'),
            'gambar'   => null,
            'role'     => 'super_admin',
        ];

        // Admin Helpdesk
        $users[] = [
            'id'       => 'USR-ADMIN-HELPDESK',
            'email'    => 'helpdesk@padang.go.id',
            'password' => Hash::make('helpdesk123'),
            'gambar'   => null,
            'role'     => 'admin_helpdesk',
        ];

        // Tim Teknis
        $users[] = [
            'id'       => 'USR-TIM-TEKNIS',
            'email'    => 'timteknis@padang.go.id',
            'password' => Hash::make('timteknis123'),
            'gambar'   => null,
            'role'     => 'tim_teknis',
        ];

        // Pimpinan
        $users[] = [
            'id'       => 'USR-PIMPINAN',
            'email'    => 'pimpinan@padang.go.id',
            'password' => Hash::make('pimpinan123'),
            'gambar'   => null,
            'role'     => 'pimpinan',
        ];

        // password = slug + 123, contoh: disdikbud123, dinkes123, dst.
        $opds = [
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
            $password = $slug . '123'; // <-- password plaintext terlihat di sini

            $this->command->line("  email: {$opd['email']} | password: {$password}");

            $users[] = [
                'id'       => 'USR-OPD-' . strtoupper($slug),
                'email'    => $opd['email'],
                'password' => Hash::make($password),
                'gambar'   => null,
                'role'     => 'opd',
            ];
        }

        DB::table('users')->insert($users);
    }
}
