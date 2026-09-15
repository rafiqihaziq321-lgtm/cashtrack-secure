<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->warn('Tiada akaun berkata laluan tetap disediakan. Gunakan php artisan cashtrack:install-owner email@example.com.');
    }
}
