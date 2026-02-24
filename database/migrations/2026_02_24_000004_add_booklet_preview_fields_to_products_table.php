<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'preview_pdf_media_id')) {
                $table->foreignId('preview_pdf_media_id')
                    ->nullable()
                    ->constrained('media')
                    ->nullOnDelete()
                    ->after('thumbnail_media_id');
            }

            if (! Schema::hasColumn('products', 'preview_image_media_ids')) {
                $table->json('preview_image_media_ids')->nullable()->after('preview_pdf_media_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'preview_image_media_ids')) {
                $table->dropColumn('preview_image_media_ids');
            }

            if (Schema::hasColumn('products', 'preview_pdf_media_id')) {
                $table->dropConstrainedForeignId('preview_pdf_media_id');
            }
        });
    }
};

