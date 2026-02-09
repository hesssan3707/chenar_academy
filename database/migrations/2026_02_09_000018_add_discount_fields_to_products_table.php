<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'discount_type') && Schema::hasColumn('products', 'discount_value')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'discount_type')) {
                $table->string('discount_type', 20)->nullable()->after('sale_price');
            }

            if (! Schema::hasColumn('products', 'discount_value')) {
                $table->unsignedInteger('discount_value')->nullable()->after('discount_type');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'discount_type') && ! Schema::hasColumn('products', 'discount_value')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'discount_value')) {
                $table->dropColumn('discount_value');
            }
            if (Schema::hasColumn('products', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
        });
    }
};
