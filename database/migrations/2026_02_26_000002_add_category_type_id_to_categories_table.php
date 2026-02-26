<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories') || ! Schema::hasTable('category_types')) {
            return;
        }

        if (! Schema::hasColumn('categories', 'category_type_id')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->foreignId('category_type_id')->nullable()->after('id')->constrained('category_types')->cascadeOnDelete();
                $table->index(['category_type_id', 'parent_id', 'sort_order']);
            });
        }

        if (Schema::hasColumn('categories', 'type')) {
            $rawTypes = DB::table('categories')->select('type')->distinct()->pluck('type')->all();
            foreach ($rawTypes as $type) {
                $key = trim((string) $type);
                if ($key === '') {
                    continue;
                }

                if ($key === 'course') {
                    $key = 'video';
                }

                DB::table('category_types')->updateOrInsert(
                    ['key' => $key],
                    ['title' => $key, 'updated_at' => now(), 'created_at' => now()],
                );
            }

            $typeIds = DB::table('category_types')->pluck('id', 'key')->all();

            foreach ($typeIds as $key => $id) {
                $key = (string) $key;
                $id = (int) $id;
                if ($id <= 0) {
                    continue;
                }

                if ($key === 'video') {
                    DB::table('categories')->whereIn('type', ['video', 'course'])->update(['category_type_id' => $id]);

                    continue;
                }

                DB::table('categories')->where('type', $key)->update(['category_type_id' => $id]);
            }
        }

        if (Schema::hasColumn('categories', 'category_type_id')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->unique(['category_type_id', 'slug'], 'categories_category_type_slug_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        if (Schema::hasColumn('categories', 'category_type_id')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->dropUnique('categories_category_type_slug_unique');
            });

            Schema::table('categories', function (Blueprint $table): void {
                $table->dropIndex(['category_type_id', 'parent_id', 'sort_order']);
                $table->dropConstrainedForeignId('category_type_id');
            });
        }
    }
};
