<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'Owner'],
            [
                'description' =>
                'Business owner with monitoring and administrative access'
            ]
        );

        Role::updateOrCreate(
            ['name' => 'Cashier'],
            [
                'description' =>
                'Employee responsible for permitted sales operations'
            ]
        );
    }
}