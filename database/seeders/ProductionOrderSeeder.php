<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Morilog\Jalali\CalendarUtils;

class ProductionOrderSeeder extends Seeder
{
    public function run(): void
    {
        $csv = ProductionSeedData::ordersCsv();
        $rows = preg_split("/\r\n|\n|\r/", $csv);
        if (! is_array($rows) || count($rows) < 2) {
            return;
        }

        $now = now();
        $headerSkipped = false;

        foreach ($rows as $line) {
            if (! is_string($line) || trim($line) === '') {
                continue;
            }

            if (! $headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $row = str_getcsv($line);
            $id = (int) ($row[0] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $state = trim((string) ($row[2] ?? ''));
            $payment = trim((string) ($row[3] ?? ''));

            $totalDiscounts = (int) ($row[8] ?? 0);
            $totalProductsIncludeTax = (int) ($row[12] ?? 0);
            $totalIncludeTax = (int) ($row[14] ?? 0);

            $addedAtRaw = trim((string) ($row[16] ?? ''));
            $updatedAtRaw = trim((string) ($row[17] ?? ''));

            $customerId = (int) ($row[21] ?? 0);
            if ($customerId <= 0 || ! DB::table('users')->where('id', $customerId)->exists()) {
                continue;
            }

            $status = Str::contains($state, 'تحویل شده') ? 'paid' : 'pending';
            $placedAt = $this->parseDateTime($addedAtRaw) ?? $now;
            $paidAt = $status === 'paid' ? ($this->parseDateTime($updatedAtRaw) ?? $placedAt) : null;

            DB::table('orders')->insert([
                'id' => $id,
                'order_number' => 'IMP-'.$id,
                'user_id' => $customerId,
                'status' => $status,
                'currency' => 'IRR',
                'subtotal_amount' => $totalProductsIncludeTax,
                'discount_amount' => $totalDiscounts,
                'total_amount' => $totalIncludeTax,
                'payable_amount' => $totalIncludeTax,
                'placed_at' => $placedAt,
                'paid_at' => $paidAt,
                'cancelled_at' => null,
                'meta' => json_encode([
                    'source_state' => $state !== '' ? $state : null,
                    'source_payment' => $payment !== '' ? $payment : null,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $placedAt,
                'updated_at' => $paidAt ?? $placedAt,
            ]);

            for ($i = 1; $i <= 6; $i++) {
                $baseIndex = 27 + (($i - 1) * 9);

                $productName = trim((string) ($row[$baseIndex + 0] ?? ''));
                $productId = (int) ($row[$baseIndex + 2] ?? 0);
                $productQuantity = (int) ($row[$baseIndex + 3] ?? 0);
                $productPrice = (int) ($row[$baseIndex + 4] ?? 0);
                $productTotal = (int) ($row[$baseIndex + 6] ?? 0);

                if ($productId <= 0 || $productQuantity <= 0) {
                    continue;
                }

                $product = DB::table('products')->where('id', $productId)->first();
                $productType = $product ? (string) $product->type : $this->detectProductType($productName, '');
                $productTitle = $product ? (string) $product->title : ($productName !== '' ? $productName : 'محصول '.$productId);

                $orderItemId = DB::table('order_items')->insertGetId([
                    'order_id' => $id,
                    'product_id' => $product ? $productId : null,
                    'product_type' => $productType,
                    'product_title' => $productTitle,
                    'quantity' => $productQuantity,
                    'unit_price' => $productPrice,
                    'total_price' => $productTotal > 0 ? $productTotal : ($productQuantity * $productPrice),
                    'currency' => 'IRR',
                    'meta' => json_encode([], JSON_THROW_ON_ERROR),
                    'created_at' => $placedAt,
                    'updated_at' => $placedAt,
                ]);

                if ($status === 'paid' && $product) {
                    DB::table('product_accesses')->updateOrInsert(
                        [
                            'user_id' => $customerId,
                            'product_id' => $productId,
                        ],
                        [
                            'order_item_id' => $orderItemId,
                            'granted_at' => $paidAt ?? $placedAt,
                            'expires_at' => null,
                            'meta' => json_encode([], JSON_THROW_ON_ERROR),
                            'created_at' => $paidAt ?? $placedAt,
                            'updated_at' => $paidAt ?? $placedAt,
                        ]
                    );
                }
            }
        }
    }

    private function detectProductType(string $title, string $description): string
    {
        if (Str::contains($title, 'جزوه')) {
            return 'note';
        }

        if (Str::contains($title, 'دوره')) {
            return 'course';
        }

        if (Str::contains($description, 'player.arvancloud.ir')) {
            return 'video';
        }

        return 'video';
    }

    private function parseDateTime(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $value);
        if (! is_array($parts) || count($parts) < 2) {
            return null;
        }

        $date = str_replace('-', '/', $parts[0]);
        $time = $parts[1];

        $year = (int) substr($date, 0, 4);

        try {
            if ($year > 0 && $year < 1700) {
                return CalendarUtils::createCarbonFromFormat('Y/m/d H:i', $date.' '.$time);
            }

            return Carbon::createFromFormat('Y/m/d H:i', $date.' '.$time);
        } catch (\Throwable) {
            return null;
        }
    }
}

