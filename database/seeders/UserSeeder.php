<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'phone' => '09377947853',
                'name' => 'Admin One',
                'first_name' => 'Admin',
                'last_name' => 'One',
                'password' => '12345678',
            ],
            [
                'phone' => '09211099564',
                'name' => 'Admin Two',
                'first_name' => 'Admin',
                'last_name' => 'Two',
                'password' => '12345678',
            ],
            [
                'phone' => '09120000001',
                'name' => 'User',
                'first_name' => null,
                'last_name' => null,
                'password' => 'password',
            ],
        ];

        $adminRole = Role::query()->firstOrCreate(['name' => 'admin'], ['description' => 'Administrator']);

        foreach ($accounts as $account) {
            $user = User::query()->firstOrCreate(
                ['phone' => $account['phone']],
                $this->buildUserAttributes([
                    'phone' => $account['phone'],
                    'name' => $account['name'],
                    'first_name' => $account['first_name'],
                    'last_name' => $account['last_name'],
                    'email' => null,
                    'password' => $account['password'],
                    'phone_verified_at' => now(),
                    'is_active' => true,
                ])
            );

            if ($account['phone'] !== '09120000001') {
                $user->roles()->syncWithoutDetaching([$adminRole->id]);
            } else {
                $user->roles()->detach($adminRole->id);
            }
        }
    }

    private function buildUserAttributes(array $data): array
    {
        $attributes = [
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'phone_verified_at' => $data['phone_verified_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        if (Schema::hasColumn('users', 'first_name')) {
            $attributes['first_name'] = $data['first_name'] ?? null;
        }

        if (Schema::hasColumn('users', 'last_name')) {
            $attributes['last_name'] = $data['last_name'] ?? null;
        }

        return $attributes;
    }
}
