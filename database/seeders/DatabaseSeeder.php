<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Local only. On the live server (AWS) run just:
     *   php artisan db:seed --class=MasterDataSeeder   (first deploy only, A2/A7)
     */
    public function run(): void
    {
        $this->call(MasterDataSeeder::class);
    }
}
