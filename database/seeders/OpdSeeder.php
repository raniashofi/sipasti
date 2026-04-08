<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpdSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('opd')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            // === OPD BIASA ===
            ['kode_opd' => '1.01.2.22.0.00.01.0000', 'nama_opd' => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',                               'kdunit' => '1.01.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'disdikbud'],
            ['kode_opd' => '1.02.0.00.0.00.01.0000', 'nama_opd' => 'DINAS KESEHATAN',                                               'kdunit' => '1.02.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dinkes'],
            ['kode_opd' => '1.02.0.00.0.00.01.0001', 'nama_opd' => 'RSUD Dr. RASIDIN',                                              'kdunit' => '1.02.0.00.0.00.01.0001.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'rsud-rasidin'],
            ['kode_opd' => '1.03.1.04.0.00.02.0000', 'nama_opd' => 'DINAS PEKERJAAN UMUM DAN PENATAAN RUANG',                       'kdunit' => '1.03.1.04.0.00.02.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dpupr'],
            ['kode_opd' => '1.04.1.03.0.00.02.0000', 'nama_opd' => 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN',                   'kdunit' => '1.04.1.03.0.00.02.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'disperkim'],
            ['kode_opd' => '1.05.0.00.0.00.01.0000', 'nama_opd' => 'SATUAN POLISI PAMONG PRAJA',                                    'kdunit' => '1.05.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'satpol-pp'],
            ['kode_opd' => '1.05.0.00.0.00.02.0000', 'nama_opd' => 'BADAN PENANGGULANGAN BENCANA DAERAH',                           'kdunit' => '1.05.0.00.0.00.02.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'bpbd'],
            ['kode_opd' => '1.05.0.00.0.00.03.0000', 'nama_opd' => 'DINAS PEMADAM KEBAKARAN',                                       'kdunit' => '1.05.0.00.0.00.03.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'damkar'],
            ['kode_opd' => '1.06.0.00.0.00.01.0000', 'nama_opd' => 'DINAS SOSIAL',                                                  'kdunit' => '1.06.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dinsos'],
            ['kode_opd' => '2.07.3.31.0.00.01.0000', 'nama_opd' => 'DINAS TENAGA KERJA DAN PERINDUSTRIAN',                          'kdunit' => '2.07.3.31.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'disnaker'],
            ['kode_opd' => '2.08.2.14.0.00.01.0000', 'nama_opd' => 'DINAS PEMBERDAYAAN PEREMPUAN, PERLINDUNGAN ANAK, PENGENDALIAN PENDUDUK, DAN KELUARGA BERENCANA',         'kdunit' => '2.08.2.14.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dp3ap2kb'],
            ['kode_opd' => '2.09.3.25.0.00.01.0000', 'nama_opd' => 'DINAS PERIKANAN DAN PANGAN',                                    'kdunit' => '2.09.3.25.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'disperikanan'],
            ['kode_opd' => '2.10.0.00.0.00.01.0000', 'nama_opd' => 'DINAS PERTANAHAN',                                              'kdunit' => '2.10.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dispertanahan'],
            ['kode_opd' => '2.11.0.00.1.04.03.0000', 'nama_opd' => 'DINAS LINGKUNGAN HIDUP',                                        'kdunit' => '2.11.1.03.1.04.03.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dlh'],
            ['kode_opd' => '2.12.0.00.0.00.01.0000', 'nama_opd' => 'DINAS KEPENDUDUKAN DAN CATATAN SIPIL',                          'kdunit' => '2.12.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'disdukcapil'],
            ['kode_opd' => '2.15.1.03.0.00.02.0000', 'nama_opd' => 'DINAS PERHUBUNGAN',                                             'kdunit' => '2.15.1.03.0.00.02.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dishub'],
            ['kode_opd' => '2.16.2.20.2.21.03.0000', 'nama_opd' => 'DINAS KOMUNIKASI DAN INFORMATIKA',                              'kdunit' => '2.16.2.20.2.21.03.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'diskominfo'],
            ['kode_opd' => '2.17.0.00.0.00.01.0000', 'nama_opd' => 'DINAS KOPERASI, USAHA KECIL, DAN MENENGAH',                       'kdunit' => '2.17.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'diskop-ukm'],
            ['kode_opd' => '2.18.0.00.0.00.01.0000', 'nama_opd' => 'DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU',                                 'kdunit' => '2.18.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dpmptsp'],
            ['kode_opd' => '2.19.8.01.0.00.01.0000', 'nama_opd' => 'DINAS PEMUDA DAN OLAHRAGA',                                     'kdunit' => '2.19.8.01.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dispora'],
            ['kode_opd' => '2.23.2.24.0.00.02.0000', 'nama_opd' => 'DINAS PERPUSTAKAAN DAN KEARSIPAN',                              'kdunit' => '2.23.2.24.0.00.02.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'disperpusip'],
            ['kode_opd' => '3.26.0.00.0.00.01.0000', 'nama_opd' => 'DINAS PARIWISATA',                                              'kdunit' => '3.26.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dispariwisata'],
            ['kode_opd' => '3.27.3.28.0.00.02.0000', 'nama_opd' => 'DINAS PERTANIAN',                                               'kdunit' => '3.27.3.28.3.28.02.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'dispertan'],
            ['kode_opd' => '3.30.5.02.0.00.01.0000', 'nama_opd' => 'DINAS PERDAGANGAN',                                             'kdunit' => '3.30.5.02.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'disdag'],
            ['kode_opd' => '4.01.0.00.0.00.01.0000', 'nama_opd' => 'SEKRETARIAT DAERAH',                                            'kdunit' => '4.01.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'setda'],
            ['kode_opd' => '4.02.0.00.0.00.01.0000', 'nama_opd' => 'SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH',                      'kdunit' => '4.02.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'sekwan'],
            ['kode_opd' => '5.01.5.05.0.00.01.0000', 'nama_opd' => 'BADAN PERENCANAAN PEMBANGUNAN DAERAH',                          'kdunit' => '5.01.5.05.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'bappeda'],
            ['kode_opd' => '5.02.0.00.0.00.01.0000', 'nama_opd' => 'BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH',                      'kdunit' => '5.02.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'bpkad'],
            ['kode_opd' => '5.02.0.00.0.00.02.0000', 'nama_opd' => 'BADAN PENDAPATAN DAERAH',                                       'kdunit' => '5.02.0.00.0.00.02.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'bapenda'],
            ['kode_opd' => '5.03.5.04.0.00.01.0000', 'nama_opd' => 'BADAN KEPEGAWAIAN DAN PENGEMBANGAN SUMBER DAYA MANUSIA',                                 'kdunit' => '5.03.5.04.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'bkpsdm'],
            ['kode_opd' => '6.01.0.00.0.00.01.0000', 'nama_opd' => 'INSPEKTORAT',                                                   'kdunit' => '6.01.0.00.0.00.01.',      'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'inspektorat'],
            // === KECAMATAN ===
            ['kode_opd' => '7.01.2.13.0.00.01.0000', 'nama_opd' => 'KECAMATAN PADANG BARAT',        'kdunit' => '7.01.2.13.0.00.01.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-padbar'],
            ['kode_opd' => '7.01.2.13.0.00.02.0000', 'nama_opd' => 'KECAMATAN PADANG TIMUR',        'kdunit' => '7.01.2.13.0.00.02.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-padtim'],
            ['kode_opd' => '7.01.2.13.0.00.03.0000', 'nama_opd' => 'KECAMATAN PADANG UTARA',        'kdunit' => '7.01.2.13.0.00.03.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-padut'],
            ['kode_opd' => '7.01.2.13.0.00.04.0000', 'nama_opd' => 'KECAMATAN PADANG SELATAN',      'kdunit' => '7.01.2.13.0.00.04.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-padsel'],
            ['kode_opd' => '7.01.2.13.0.00.05.0000', 'nama_opd' => 'KECAMATAN NANGGALO',            'kdunit' => '7.01.2.13.0.00.05.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-nanggalo'],
            ['kode_opd' => '7.01.2.13.0.00.06.0000', 'nama_opd' => 'KECAMATAN KURANJI',             'kdunit' => '7.01.2.13.0.00.06.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-kuranji'],
            ['kode_opd' => '7.01.2.13.0.00.07.0000', 'nama_opd' => 'KECAMATAN LUBUK BEGALUNG',      'kdunit' => '7.01.2.13.0.00.07.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-lubeg'],
            ['kode_opd' => '7.01.2.13.0.00.08.0000', 'nama_opd' => 'KECAMATAN LUBUK KILANGAN',      'kdunit' => '7.01.2.13.0.00.08.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-lubuk-kilangan'],
            ['kode_opd' => '7.01.2.13.0.00.09.0000', 'nama_opd' => 'KECAMATAN PAUH',                'kdunit' => '7.01.2.13.0.00.09.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-pauh'],
            ['kode_opd' => '7.01.2.13.0.00.10.0000', 'nama_opd' => 'KECAMATAN KOTO TANGAH',         'kdunit' => '7.01.2.13.0.00.10.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-kotangah'],
            ['kode_opd' => '7.01.2.13.0.00.11.0000', 'nama_opd' => 'KECAMATAN BUNGUS TELUK KABUNG', 'kdunit' => '7.01.2.13.0.00.11.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kec-bungus'],
            // === BADAN ===
            ['kode_opd' => '8.01.0.00.0.00.01.0000', 'nama_opd' => 'BADAN KESATUAN BANGSA DAN POLITIK', 'kdunit' => '8.01.0.00.0.00.01.', 'parent_id' => null, 'is_bagian' => 'N', 'slug' => 'kesbangpol'],

            // === BAGIAN SETDA ===
            // PERBAIKAN: Ubah parent_id 25 menjadi string 'OPD-SETDA'
            ['kode_opd' => '4.01.0.00.0.00.01.0001', 'nama_opd' => 'BAGIAN HUKUM',                     'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-hukum'],
            ['kode_opd' => '4.01.0.00.0.00.01.0002', 'nama_opd' => 'BAGIAN UMUM',                      'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-umum'],
            ['kode_opd' => '4.01.0.00.0.00.01.0003', 'nama_opd' => 'BAGIAN PEREKONOMIAN',              'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-perekonomian'],
            ['kode_opd' => '4.01.0.00.0.00.01.0004', 'nama_opd' => 'BAGIAN ORGANISASI',                'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-organisasi'],
            ['kode_opd' => '4.01.0.00.0.00.01.0005', 'nama_opd' => 'BAGIAN PROTOKOL',                  'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-protokol'],
            ['kode_opd' => '4.01.0.00.0.00.01.0006', 'nama_opd' => 'BAGIAN KEUANGAN',                  'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-keuangan'],
            ['kode_opd' => '4.01.0.00.0.00.01.0008', 'nama_opd' => 'BAGIAN PENGADAAN BARANG DAN JASA', 'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-pbj'],
            ['kode_opd' => '4.01.0.00.0.00.01.0009', 'nama_opd' => 'BAGIAN PERENCANAAN',               'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-perencanaan'],
            ['kode_opd' => '4.01.0.00.0.00.01.0010', 'nama_opd' => 'BAGIAN KERJASAMA',                 'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-kerjasama'],
            ['kode_opd' => '4.01.0.00.0.00.01.0011', 'nama_opd' => 'BAGIAN ADMINISTRASI PEMBANGUNAN',  'kdunit' => null, 'parent_id' => 'OPD-SETDA', 'is_bagian' => 'Y', 'slug' => 'bag-adm-pembangunan'],
        ];

        $rows = [];
        foreach ($data as $item) {
            $slug = $item['slug'];
            $rows[] = [
                'id'             => 'OPD-' . strtoupper($slug),
                'user_id'        => 'USR-OPD-' . strtoupper($slug),
                'kode_opd'       => $item['kode_opd'],
                'nama_opd'       => $item['nama_opd'],
                'kdunit'         => $item['kdunit'],
                'parent_id'      => $item['parent_id'],
                'is_bagian'      => $item['is_bagian'],
            ];
        }

        DB::table('opd')->insert($rows);
    }
}
