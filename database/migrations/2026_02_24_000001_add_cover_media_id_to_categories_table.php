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

        if (Schema::hasColumn('categories', 'cover_media_id')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete()
                ->after('description');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        if (! Schema::hasColumn('categories', 'cover_media_id')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cover_media_id');
        });
    }
};

