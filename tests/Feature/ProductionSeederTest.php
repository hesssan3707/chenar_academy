<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_uses_default_seeders_when_not_production(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'phone' => '09120000000',
        ]);
    }

    public function test_database_seeder_uses_production_seeders_and_purges_data_in_production(): void
    {
        DB::table('users')->insert([
            'id' => 999,
            'name' => 'Temp',
            'first_name' => null,
            'last_name' => null,
            'phone' => '09990000000',
            'phone_verified_at' => now(),
            'email' => null,
            'email_verified_at' => null,
            'password' => null,
            'is_active' => true,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->app['env'] = 'production';
        $this->artisan('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => 999,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => 15,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => 1065,
        ]);

        $this->assertDatabaseHas('categories', [
            'type' => 'institution',
            'slug' => 'pnu',
        ]);
        $this->assertDatabaseHas('categories', [
            'type' => 'institution',
            'slug' => 'state',
        ]);
        $this->assertDatabaseHas('categories', [
            'type' => 'institution',
            'slug' => 'iau',
        ]);
        $this->assertSame(
            3,
            DB::table('categories')->where('type', 'institution')->count()
        );

        $this->assertSame(5, DB::table('category_types')->count());

        $this->assertDatabaseMissing('users', [
            'phone' => '09120000000',
        ]);
    }
}
