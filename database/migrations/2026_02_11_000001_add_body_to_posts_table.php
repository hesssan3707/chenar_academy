<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        if (Schema::hasColumn('posts', 'body')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->longText('body')->nullable()->after('excerpt');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        if (! Schema::hasColumn('posts', 'body')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('body');
        });
    }
};
