<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasTable('media')) {
            return;
        }

        if (! Schema::hasTable('booklets')) {
            Schema::create('booklets', function (Blueprint $table) {
                $table->foreignId('product_id')->primary()->constrained('products')->cascadeOnDelete();
                $table->foreignId('file_media_id')->nullable()->constrained('media')->nullOnDelete();
                $table->foreignId('sample_pdf_media_id')->nullable()->constrained('media')->nullOnDelete();
                $table->json('preview_image_media_ids')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        $productIds = DB::table('products')->where('type', 'note')->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($productIds !== []) {
            foreach ($productIds as $productId) {
                $existing = DB::table('booklets')->where('product_id', $productId)->exists();
                if ($existing) {
                    continue;
                }

                $payload = [
                    'product_id' => $productId,
                    'file_media_id' => null,
                    'sample_pdf_media_id' => null,
                    'preview_image_media_ids' => null,
                    'meta' => json_encode([], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('products', 'preview_pdf_media_id')) {
                    $sampleId = (int) (DB::table('products')->where('id', $productId)->value('preview_pdf_media_id') ?? 0);
                    $payload['sample_pdf_media_id'] = $sampleId > 0 ? $sampleId : null;
                }

                if (Schema::hasColumn('products', 'preview_image_media_ids')) {
                    $preview = DB::table('products')->where('id', $productId)->value('preview_image_media_ids');
                    if (is_string($preview) && trim($preview) !== '') {
                        $payload['preview_image_media_ids'] = $preview;
                    }
                }

                if (Schema::hasTable('product_parts')) {
                    $fileMediaId = (int) (DB::table('product_parts')
                        ->where('product_id', $productId)
                        ->where('part_type', 'file')
                        ->whereNotNull('media_id')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->value('media_id') ?? 0);

                    $payload['file_media_id'] = $fileMediaId > 0 ? $fileMediaId : null;
                }

                DB::table('booklets')->insert($payload);
            }

            if (Schema::hasTable('product_parts')) {
                DB::table('product_parts')
                    ->whereIn('product_id', $productIds)
                    ->where('part_type', 'file')
                    ->delete();
            }
        }

        if (Schema::hasColumn('products', 'preview_image_media_ids')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('preview_image_media_ids');
            });
        }

        if (Schema::hasColumn('products', 'preview_pdf_media_id')) {
            $driver = DB::connection()->getDriverName();
            Schema::table('products', function (Blueprint $table) use ($driver): void {
                // SQLite cannot drop foreign keys directly.
                if ($driver === 'sqlite') {
                    $table->dropColumn('preview_pdf_media_id');

                    return;
                }

                $table->dropConstrainedForeignId('preview_pdf_media_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booklets')) {
            Schema::dropIfExists('booklets');
        }
    }
};
