<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPhone = '09120000000';
        $userPhone = '09120000001';

        $admin = User::query()->firstOrCreate(['phone' => $adminPhone], [
            'name' => 'Admin',
            'email' => null,
            'password' => 'password',
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $user = User::query()->firstOrCreate(['phone' => $userPhone], [
            'name' => 'User',
            'email' => null,
            'password' => 'password',
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $adminRole = Role::query()->firstOrCreate(['name' => 'admin'], ['description' => 'Administrator']);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        $user->roles()->detach($adminRole->id);
    }
}
