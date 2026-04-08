<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BidangSeeder::class,
            UserSeeder::class,
            OpdSeeder::class,
            TimTeknisSeeder::class,
            AdminHelpdeskSeeder::class,
            PimpinanSeeder::class,
        ]);
    }
}
