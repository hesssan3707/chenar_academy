<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->call([
                ProductionDatabaseSeeder::class,
            ]);

            return;
        }

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
