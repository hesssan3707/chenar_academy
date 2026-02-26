<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        if (Schema::hasColumn('categories', 'type')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->dropUnique(['type', 'slug']);
            });

            Schema::table('categories', function (Blueprint $table): void {
                $table->dropIndex(['type', 'parent_id', 'sort_order']);
            });

            Schema::table('categories', function (Blueprint $table): void {
                $table->dropColumn('type');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        if (! Schema::hasColumn('categories', 'type')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->string('type', 20)->nullable()->after('id');
            });
        }
    }
};
