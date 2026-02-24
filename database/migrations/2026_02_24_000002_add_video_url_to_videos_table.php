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

        if (Schema::hasColumn('videos', 'video_url')) {
            return;
        }

        Schema::table('videos', function (Blueprint $table): void {
            $table->string('video_url', 2048)->nullable()->after('media_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('videos')) {
            return;
        }

        if (! Schema::hasColumn('videos', 'video_url')) {
            return;
        }

        Schema::table('videos', function (Blueprint $table): void {
            $table->dropColumn('video_url');
        });
    }
};
