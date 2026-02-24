<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeDatabase();

        $this->call([
            ProductionCategorySeeder::class,
            ProductionUserSeeder::class,
            ProductionProductSeeder::class,
            ProductionOrderSeeder::class,
        ]);
    }

    private function purgeDatabase(): void
    {
        Schema::disableForeignKeyConstraints();

        $driver = DB::connection()->getDriverName();
        $tables = [];

        if ($driver === 'sqlite') {
            $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table'"))
                ->map(fn ($row) => (string) $row->name)
                ->filter(fn (string $name) => $name !== 'sqlite_sequence')
                ->values()
                ->all();
        } elseif ($driver === 'mysql') {
            $tables = collect(DB::select('SHOW TABLES'))
                ->map(function ($row) {
                    $values = array_values((array) $row);

                    return (string) ($values[0] ?? '');
                })
                ->filter()
                ->values()
                ->all();
        } else {
            $tables = collect(DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()'))
                ->map(fn ($row) => (string) $row->table_name)
                ->filter()
                ->values()
                ->all();
        }

        $tablesToClear = collect($tables)
            ->reject(fn (string $name) => $name === 'migrations')
            ->values()
            ->all();

        foreach ($tablesToClear as $table) {
            if ($driver === 'mysql') {
                DB::table($table)->truncate();
            } else {
                DB::table($table)->delete();
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
