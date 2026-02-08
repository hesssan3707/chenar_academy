<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_parts')) {
            return;
        }

        Schema::create('product_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('part_type', 20)->default('text');
            $table->string('title')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->longText('content')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
            $table->index(['product_id', 'part_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_parts');
    }
};
