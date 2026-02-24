<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductionUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $adminRoleId = $this->ensureAdminRole($now);
        $this->seedAdminUser($adminRoleId, $now);
        $this->seedUsersFromEmbeddedData($now);
    }

    private function ensureAdminRole(\Illuminate\Support\Carbon $now): int
    {
        $existingId = DB::table('roles')->where('name', 'admin')->value('id');
        if ($existingId) {
            return (int) $existingId;
        }

        return (int) DB::table('roles')->insertGetId([
            'name' => 'admin',
            'description' => 'Administrator',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedAdminUser(int $adminRoleId, \Illuminate\Support\Carbon $now): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Admin',
            'first_name' => null,
            'last_name' => null,
            'phone' => '09100000000',
            'phone_verified_at' => $now,
            'email' => null,
            'email_verified_at' => null,
            'password' => null,
            'is_active' => true,
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('user_roles')->insert([
            'user_id' => 1,
            'role_id' => $adminRoleId,
        ]);
    }

    private function seedUsersFromEmbeddedData(\Illuminate\Support\Carbon $now): void
    {
        $csv = ProductionSeedData::customersCsv();
        $rows = preg_split("/\r\n|\n|\r/", $csv);
        if (! is_array($rows) || count($rows) < 2) {
            return;
        }

        $seenPhones = [];
        $seenEmails = [];
        $headerSkipped = false;

        foreach ($rows as $line) {
            if (! is_string($line) || trim($line) === '') {
                continue;
            }

            if (! $headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $row = str_getcsv($line);
            $id = (int) ($row[0] ?? 0);
            if ($id <= 0 || $id === 1) {
                continue;
            }

            $firstName = trim((string) ($row[1] ?? ''));
            $lastName = trim((string) ($row[2] ?? ''));
            $email = trim((string) ($row[5] ?? ''));
            $phoneRaw = trim((string) ($row[6] ?? ''));

            $phone = $this->normalizePhone($phoneRaw);
            if (! $phone) {
                $phone = '09'.str_pad((string) $id, 9, '0', STR_PAD_LEFT);
            }

            if (isset($seenPhones[$phone]) || DB::table('users')->where('phone', $phone)->exists()) {
                $phone = '09'.str_pad((string) $id, 9, '0', STR_PAD_LEFT);
            }
            $seenPhones[$phone] = true;

            $emailValue = null;
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailLower = mb_strtolower($email);
                if (! isset($seenEmails[$emailLower]) && ! DB::table('users')->where('email', $emailLower)->exists()) {
                    $emailValue = $emailLower;
                    $seenEmails[$emailLower] = true;
                }
            }

            $name = trim($firstName.' '.$lastName);
            if ($name === '') {
                $name = 'کاربر '.$id;
            }

            DB::table('users')->insert([
                'id' => $id,
                'name' => $name,
                'first_name' => $firstName !== '' ? $firstName : null,
                'last_name' => $lastName !== '' ? $lastName : null,
                'phone' => $phone,
                'phone_verified_at' => $now,
                'email' => $emailValue,
                'email_verified_at' => null,
                'password' => null,
                'is_active' => true,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function normalizePhone(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if (! is_string($digits)) {
            return null;
        }

        if ($digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '98')) {
            $digits = '0'.substr($digits, 2);
        }

        if (strlen($digits) === 10 && Str::startsWith($digits, '9')) {
            $digits = '0'.$digits;
        }

        if (strlen($digits) < 10) {
            return null;
        }

        return $digits;
    }
}

