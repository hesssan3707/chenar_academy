<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('videos')) {
            return;
        }

        if (Schema::hasColumn('videos', 'preview_media_id')) {
            return;
        }

        Schema::table('videos', function (Blueprint $table) {
            $table->foreignId('preview_media_id')->nullable()->after('media_id')->constrained('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('videos')) {
            return;
        }

        if (! Schema::hasColumn('videos', 'preview_media_id')) {
            return;
        }

        Schema::table('videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preview_media_id');
        });
    }
};
