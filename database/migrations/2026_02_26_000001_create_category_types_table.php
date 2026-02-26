<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('category_types')) {
            return;
        }

        Schema::create('category_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 20)->unique();
            $table->string('title', 80);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_types');
    }
};
