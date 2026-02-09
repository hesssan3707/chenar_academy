<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        if (
            Schema::hasColumn('product_reviews', 'status')
            && Schema::hasColumn('product_reviews', 'moderated_at')
        ) {
            return;
        }

        Schema::table('product_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('product_reviews', 'status')) {
                $table->string('status', 20)->default('approved')->after('body');
            }

            if (! Schema::hasColumn('product_reviews', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        if (! Schema::hasColumn('product_reviews', 'status') && ! Schema::hasColumn('product_reviews', 'moderated_at')) {
            return;
        }

        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'moderated_at')) {
                $table->dropColumn('moderated_at');
            }
            if (Schema::hasColumn('product_reviews', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
