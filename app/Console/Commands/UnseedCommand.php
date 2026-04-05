<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UnseedCommand extends Command
{
    protected $signature   = 'db:unseed';
    protected $description = 'Hapus semua data hasil seeder';

    public function handle(): void
    {
        if (!$this->confirm('Yakin ingin menghapus semua data seeder?')) {
            $this->info('Dibatalkan.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('opd')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Semua data seeder berhasil dihapus.');
    }
}
