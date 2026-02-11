<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'institution_category_id')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('institution_category_id')
                ->nullable()
                ->after('thumbnail_media_id')
                ->constrained('categories')
                ->nullOnDelete();

            $table->index(['institution_category_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'institution_category_id')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['institution_category_id']);
            $table->dropConstrainedForeignId('institution_category_id');
        });
    }
};
