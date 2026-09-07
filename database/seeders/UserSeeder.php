<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('name', 'Owner')->first();
        $cashierRole = Role::where('name', 'Cashier')->first();

        User::updateOrCreate(
            ['email' => 'owner@cashtrack.test'],
            [
                'name' => 'System Owner',
                'password' => Hash::make('password123'),
                'role_id' => $ownerRole->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'cashier@cashtrack.test'],
            [
                'name' => 'System Cashier',
                'password' => Hash::make('password123'),
                'role_id' => $cashierRole->id,
                'is_active' => true,
            ]
        );
    }
}