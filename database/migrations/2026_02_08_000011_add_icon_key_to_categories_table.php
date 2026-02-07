<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('categories', 'icon_key')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('icon_key', 50)->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'icon_key')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('icon_key');
        });
    }
};
