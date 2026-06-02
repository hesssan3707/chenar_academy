<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CsvImportSeeder extends Seeder
{
    private const IMPORT_CURRENCY = 'IRT';

    private array $report = [
        'admins' => ['created' => 0, 'skipped' => 0],
        'customers' => ['imported' => 0, 'skipped' => 0, 'errors' => []],
        'products' => ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []],
        'orders' => ['imported' => 0, 'skipped' => 0, 'errors' => []],
        'payments' => ['imported' => 0, 'skipped' => 0, 'errors' => []],
        'categories' => ['created' => 0],
    ];

    private array $userCache = [];
    private array $categoryCache = [];

    public function run(): void
    {
        $this->seedAdminUsers();
        $this->importCustomers();
        $this->importProducts();
        $this->importOrders();
        $this->printReport();
    }

    private function seedAdminUsers(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );

        $accounts = [
            [
                'first_name' => 'Admin',
                'last_name' => 'One',
                'name' => 'Admin One',
                'phone' => '09377947853',
                'password' => '12345678',
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'Two',
                'name' => 'Admin Two',
                'phone' => '09211099564',
                'password' => '12345678',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrCreate(
                ['phone' => $account['phone']],
                $this->buildUserAttributes([
                    'name' => $account['name'],
                    'first_name' => $account['first_name'],
                    'last_name' => $account['last_name'],
                    'email' => null,
                    'password' => $account['password'],
                    'phone_verified_at' => now(),
                    'is_active' => true,
                ])
            );

            $user->roles()->syncWithoutDetaching([$adminRole->id]);

            if ($user->wasRecentlyCreated) {
                $this->report['admins']['created']++;
            } else {
                $this->report['admins']['skipped']++;
            }
        }
    }

    private function importCustomers(): void
    {
        $filePath = database_path('customers.csv');
        if (! File::exists($filePath)) {
            $this->command->warn('customers.csv not found, skipping customer import.');
            return;
        }

        $rows = $this->readCsvRows($filePath);
        if (count($rows) < 2) {
            return;
        }

        $header = $rows[0];

        foreach (array_slice($rows, 1) as $index => $row) {
            if (count($row) === 0 || $this->isRowEmpty($row)) {
                continue;
            }

            $data = $this->mapRow($header, $row);
            $customerId = (int) ($data['id'] ?? 0);
            $firstName = trim((string) ($data['first_name'] ?? ''));
            $lastName = trim((string) ($data['last_name'] ?? ''));
            $email = $this->normalizeEmail($data['email'] ?? '');
            $phone = $this->normalizePhone($data['mobile'] ?? '');

            if ($phone === null) {
                $phone = '09'.str_pad((string) max(0, $customerId), 9, '0', STR_PAD_LEFT);
            }

            $name = trim($firstName.' '.$lastName);
            if ($name === '') {
                $name = 'User';
            }

            try {
                $user = $this->findExistingUser($phone, $email);
                if ($user) {
                    $changed = false;
                    if ($user->name !== $name) {
                        $user->name = $name;
                        $changed = true;
                    }
                    if ($email !== null && $user->email !== $email) {
                        $user->email = $email;
                        $changed = true;
                    }
                    if ($user->phone_verified_at === null) {
                        $user->phone_verified_at = now();
                        $changed = true;
                    }
                    if ($changed) {
                        $user->save();
                    }

                    // Treat existing row as imported (update) so every CSV row is processed.
                    $this->report['customers']['imported']++;
                } else {
                    $user = User::query()->create($this->buildUserAttributes([
                        'name' => $name,
                        'first_name' => $firstName !== '' ? $firstName : null,
                        'last_name' => $lastName !== '' ? $lastName : null,
                        'phone' => $phone,
                        'email' => $email,
                        'password' => null,
                        'phone_verified_at' => now(),
                        'is_active' => true,
                    ]));
                    $this->report['customers']['imported']++;
                }

                $this->cacheUser($user);
            } catch (Throwable $exception) {
                $this->report['customers']['errors'][] = sprintf(
                    'Row %d: %s',
                    $index + 2,
                    $exception->getMessage()
                );
            }
        }
    }

    private function buildUserAttributes(array $data): array
    {
        $attributes = [
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'phone_verified_at' => $data['phone_verified_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        if (Schema::hasColumn('users', 'first_name')) {
            $attributes['first_name'] = $data['first_name'] ?? null;
        }

        if (Schema::hasColumn('users', 'last_name')) {
            $attributes['last_name'] = $data['last_name'] ?? null;
        }

        return $attributes;
    }

    private function importProducts(): void
    {
        $filePath = database_path('products.csv');
        if (! File::exists($filePath)) {
            $this->command->warn('products.csv not found, skipping product import.');
            return;
        }

        $rows = $this->readCsvRows($filePath);
        if (count($rows) < 2) {
            return;
        }

        $header = $rows[0];

        foreach (array_slice($rows, 1) as $index => $row) {
            if ($this->isRowEmpty($row)) {
                continue;
            }

            $data = $this->mapRow($header, $row);
            $title = trim((string) ($data['title'] ?? ''));
            if ($title === '') {
                $this->report['products']['skipped']++;
                continue;
            }

            $sourceId = $this->normalizeInteger($data['id'] ?? null);
            $slug = trim((string) ($data['slug'] ?? ''));
            if ($slug === '') {
                $slug = $this->uniqueSlugFrom($title, $sourceId, 'product');
            }

            $status = $this->normalizeBoolean($data['visible'] ?? 'FALSE') ? 'published' : 'draft';
            $basePrice = $this->normalizeInteger($data['price'] ?? 0);
            $discountType = null;
            $discountValue = 0;
            $saleMode = strtolower(trim((string) ($data['saleDiscountMode'] ?? '')));
            $saleDiscount = $this->normalizeInteger($data['saleDiscount'] ?? 0);
            if ($saleMode === 'percent' && $saleDiscount > 0) {
                $discountType = 'percent';
                $discountValue = $saleDiscount;
            } elseif ($saleMode === 'amount' && $saleDiscount > 0) {
                $discountType = 'amount';
                $discountValue = $saleDiscount;
            }

            $productAttributes = [
                'type' => Str::lower(trim((string) ($data['product_type'] ?? 'virtual'))),
                'title' => $title,
                'excerpt' => trim((string) ($data['description'] ?? '')),
                'description' => trim((string) ($data['description'] ?? '')),
                'status' => $status,
                'base_price' => $basePrice,
                'sale_price' => null,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'currency' => self::IMPORT_CURRENCY,
                'published_at' => $status === 'published' ? now() : null,
                'meta' => array_filter([ 
                    'source_id' => $sourceId > 0 ? $sourceId : null,
                    'sku' => trim((string) ($data['sku'] ?? '')) ?: null,
                    'barcode' => trim((string) ($data['barcode'] ?? '')) ?: null,
                    'ribbon' => trim((string) ($data['ribbon'] ?? '')) ?: null,
                    'inventory' => trim((string) ($data['inventory'] ?? '')) ?: null,
                    'quantity' => trim((string) ($data['quantity'] ?? '')) ?: null,
                    'weight' => trim((string) ($data['weight'] ?? '')) ?: null,
                    'categories' => trim((string) ($data['categories'] ?? '')) ?: null,
                    'category_names' => trim((string) ($data['categoryNames'] ?? '')) ?: null,
                ]),
            ];

            try {
                $product = Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    $productAttributes
                );

                if ($sourceId > 0) {
                    $meta = is_array($product->meta) ? $product->meta : [];
                    if (($meta['source_id'] ?? null) !== $sourceId) {
                        $meta['source_id'] = $sourceId;
                        $product->meta = $meta;
                        $product->save();
                    }
                }

                $categoryIds = $this->resolveProductCategoryIds($data);
                if ($categoryIds !== []) {
                    $product->categories()->syncWithoutDetaching($categoryIds);
                }

                if ($product->wasRecentlyCreated) {
                    $this->report['products']['imported']++;
                } else {
                    $this->report['products']['updated']++;
                }
            } catch (Throwable $exception) {
                $this->report['products']['errors'][] = sprintf(
                    'Row %d: %s',
                    $index + 2,
                    $exception->getMessage()
                );
            }
        }
    }

    private function importOrders(): void
    {
        $filePath = database_path('orders.csv');
        if (! File::exists($filePath)) {
            $this->command->warn('orders.csv not found, skipping order import.');
            return;
        }

        $rows = $this->readCsvRows($filePath);
        if (count($rows) < 2) {
            return;
        }

        $header = $rows[0];

        foreach (array_slice($rows, 1) as $index => $row) {
            if ($this->isRowEmpty($row)) {
                continue;
            }

            $data = $this->mapRow($header, $row);
            $sourceId = trim((string) ($data['id'] ?? ''));
            if ($sourceId === '') {
                $this->report['orders']['skipped']++;
                continue;
            }

            try {
                DB::transaction(function () use ($data, $sourceId) {
                    $orderNumber = 'csv-'.$sourceId;
                    $customerPhone = $this->normalizePhone($data['customer_mobile'] ?? '');
                    $customerEmail = $this->normalizeEmail($data['customer_email'] ?? '');
                    $customerName = trim((string) ($data['customer_name'] ?? ''));

                    $user = $this->resolveOrderUser($customerPhone, $customerEmail, $customerName, $sourceId);
                    if (! $user) {
                        throw new \RuntimeException('Unable to resolve user for order '.$sourceId);
                    }

                    $status = $this->mapOrderStatus((string) ($data['state'] ?? ''), (string) ($data['payment'] ?? ''));
                    $subtotal = $this->normalizeInteger($data['total_products_exclude_tax'] ?? $data['total_products_include_tax'] ?? 0);
                    $discountAmount = $this->normalizeInteger($data['total_discounts'] ?? 0);
                    $totalAmount = $this->normalizeInteger($data['total_include_tax'] ?? $data['total_exclude_tax'] ?? 0);
                    $payableAmount = $this->normalizeInteger($data['total_include_tax'] ?? $data['total_exclude_tax'] ?? 0);

                    $order = Order::query()->updateOrCreate(
                        ['order_number' => $orderNumber],
                        [
                            'user_id' => $user->id,
                            'status' => $status,
                            'currency' => self::IMPORT_CURRENCY,
                            'subtotal_amount' => $subtotal,
                            'discount_amount' => $discountAmount,
                            'total_amount' => $totalAmount,
                            'payable_amount' => $payableAmount,
                            'placed_at' => null,
                            'paid_at' => $status !== 'pending' ? now() : null,
                            'cancelled_at' => $status === 'cancelled' ? now() : null,
                            'meta' => array_filter([ 
                                'source_id' => $sourceId,
                                'customer_id' => trim((string) ($data['customer_id'] ?? '')),
                                'customer_name' => $customerName,
                                'customer_national_code' => trim((string) ($data['customer_national_code'] ?? '')),
                                'customer_mobile' => $customerPhone,
                                'customer_email' => $customerEmail,
                                'raw_state' => trim((string) ($data['state'] ?? '')),
                                'raw_payment' => trim((string) ($data['payment'] ?? '')),
                                'source_order_date' => trim((string) ($data['added_at'] ?? '')),
                            ]),
                        ]
                    );

                    $this->createOrderItems($order, $data);
                    $this->createPaymentForOrder($order, $data);

                    // If the order is paid, grant product access to the purchaser.
                    if ($order->status === 'paid') {
                        $this->grantAccessForOrder($order);
                    }

                    if ($order->wasRecentlyCreated) {
                        $this->report['orders']['imported']++;
                    }
                });
            } catch (Throwable $exception) {
                $this->report['orders']['errors'][] = sprintf('Order %s: %s', $sourceId, $exception->getMessage());
            }
        }
    }

    private function createOrderItems(Order $order, array $data): void
    {
        for ($index = 1; $index <= 6; $index++) {
            $title = trim((string) ($data["productName{$index}"] ?? ''));
            $quantity = $this->normalizeInteger($data["productQuantity{$index}"] ?? 0);
            if ($quantity <= 0 || $title === '') {
                continue;
            }

            $sourceProductId = trim((string) ($data["productId{$index}"] ?? ''));
            $product = null;
            if ($sourceProductId !== '') {
                $product = Product::query()->where('meta->source_id', $sourceProductId)->first();
            }

            $unitPrice = $this->normalizeInteger($data["productPrice{$index}"] ?? 0);
            $totalPrice = $this->normalizeInteger($data["productTotal{$index}"] ?? ($unitPrice * $quantity));
            $productType = trim((string) ($data["productType{$index}"] ?? ($product?->type ?? '')));

            OrderItem::query()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_title' => $title,
                    'product_id' => $product?->id,
                ],
                [
                    'product_type' => $productType !== '' ? $productType : ($product?->type ?? 'virtual'),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'currency' => self::IMPORT_CURRENCY,
                    'meta' => array_filter([
                        'source_product_id' => $sourceProductId !== '' ? $sourceProductId : null,
                        'sku' => trim((string) ($data["productSku{$index}"] ?? '')) ?: null,
                        'compare_price' => $this->normalizeInteger($data["productComparePrice{$index}"] ?? 0),
                        'weight' => $this->normalizeInteger($data["productWeight{$index}"] ?? 0),
                        'fields' => trim((string) ($data["fields{$index}"] ?? '')) ?: null,
                    ]),
                ]
            );
        }
    }

    private function createPaymentForOrder(Order $order, array $data): void
    {
        $gateway = $this->normalizePaymentGateway((string) ($data['payment'] ?? ''));
        $status = $this->mapPaymentStatus((string) ($data['state'] ?? ''), (string) ($data['payment'] ?? ''));
        // If the order is considered paid, ensure the payment record reflects that.
        $paymentStatus = $order->status === 'paid' ? 'paid' : $status;
        $amount = $this->normalizeInteger($data['total_include_tax'] ?? $data['total_exclude_tax'] ?? 0);

        $payment = Payment::query()->updateOrCreate(
            [
                'order_id' => $order->id,
                'gateway' => $gateway,
                'amount' => $amount,
            ],
            [
                'status' => $paymentStatus,
                'currency' => self::IMPORT_CURRENCY,
                'authority' => null,
                'reference_id' => null,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                'meta' => array_filter([
                    'raw_payment' => trim((string) ($data['payment'] ?? '')),
                    'raw_state' => trim((string) ($data['state'] ?? '')),
                ]),
            ]
        );

        if ($payment->wasRecentlyCreated) {
            $this->report['payments']['imported']++;
        }
    }

    private function resolveOrderUser(?string $phone, ?string $email, string $name, string $sourceOrderId): ?User
    {
        $user = $this->findExistingUser($phone, $email);
        if ($user) {
            return $user;
        }

        $phone = $phone ?? '09'.str_pad($sourceOrderId, 9, '0', STR_PAD_LEFT);
        $user = User::query()->create([
            'name' => $name !== '' ? $name : 'User',
            'phone' => $phone,
            'email' => $email,
            'password' => null,
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->cacheUser($user);

        return $user;
    }

    private function findExistingUser(?string $phone, ?string $email): ?User
    {
        $cacheKey = 'phone:'.($phone ?? '').'|email:'.($email ?? '');
        if (isset($this->userCache[$cacheKey])) {
            return $this->userCache[$cacheKey];
        }

        $query = User::query();
        if ($phone !== null) {
            $query->orWhere('phone', $phone);
        }
        if ($email !== null) {
            $query->orWhere('email', $email);
        }

        $user = $query->first();
        if ($user) {
            $this->cacheUser($user);
        }

        return $user;
    }

    private function cacheUser(User $user): void
    {
        if ($user->phone) {
            $this->userCache['phone:'.$user->phone] = $user;
        }
        if ($user->email) {
            $this->userCache['email:'.mb_strtolower($user->email)] = $user;
        }
    }

    private function resolveProductCategoryIds(array $data): array
    {
        $categoryIds = [];
        $rawNames = trim((string) ($data['categoryNames'] ?? ''));
        if ($rawNames !== '') {
            foreach (preg_split('/\s*,\s*/u', $rawNames) as $name) {
                $id = $this->ensureCategory($name);
                if ($id !== null) {
                    $categoryIds[] = $id;
                }
            }
        }

        if ($categoryIds === [] && isset($data['categories'])) {
            $rawIds = trim((string) ($data['categories'] ?? ''));
            foreach (preg_split('/\s*,\s*/u', $rawIds) as $rawId) {
                $rawId = trim($rawId);
                if ($rawId === '') {
                    continue;
                }
                $categoryIds[] = $this->ensureCategory('category-'.$rawId);
            }
        }

        return array_values(array_unique(array_filter($categoryIds)));
    }

    private function ensureCategory(string $title): ?int
    {
        $title = trim($title);
        if ($title === '') {
            return null;
        }

        $slug = Str::slug($title);
        if ($slug === '') {
            $slug = 'category-'.md5($title);
        }

        $cacheKey = 'category:'.$slug;
        if (isset($this->categoryCache[$cacheKey])) {
            return $this->categoryCache[$cacheKey];
        }

        $category = Category::query()
            ->where('slug', $slug)
            ->where('category_type_id', Category::typeId('product'))
            ->first();

        if (! $category) {
            $category = Category::query()->create([
                'type' => 'product',
                'title' => $title,
                'slug' => $slug,
                'description' => null,
                'is_active' => true,
                'sort_order' => 0,
            ]);
            $this->report['categories']['created']++;
        }

        $this->categoryCache[$cacheKey] = $category->id;

        return $category->id;
    }

    private function mapRow(array $header, array $row): array
    {
        $mapped = [];
        foreach ($header as $index => $key) {
            if (! isset($row[$index])) {
                continue;
            }
            $mapped[$key] = $row[$index];
        }

        return $mapped;
    }

    private function readCsvRows(string $path): array
    {
        $lines = File::lines($path)->map(fn ($line) => trim($line))->filter()->all();
        $rows = [];
        foreach ($lines as $line) {
            $line = preg_replace('/\x{FEFF}/u', '', $line);
            $rows[] = str_getcsv($line);
        }

        return $rows;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeEmail(?string $value): ?string
    {
        $email = trim((string) $value);
        if ($email === '') {
            return null;
        }

        $email = mb_strtolower($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function normalizePhone(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '98')) {
            $digits = '0'.substr($digits, 2);
        }

        if (strlen($digits) === 10 && Str::startsWith($digits, '9')) {
            $digits = '0'.$digits;
        }

        if (strlen($digits) !== 11 || ! Str::startsWith($digits, '09')) {
            return null;
        }

        return $digits;
    }

    private function normalizeInteger($value): int
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9\-]/', '', $value);

        return (int) $value;
    }

    private function normalizeBoolean($value): bool
    {
        $value = mb_strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 't', 'yes', 'y', 'on', 'true', 'TRUE', 'TRUE'], true) || $value === 'true' || $value === 'TRUE' || $value === 'yes' || $value === 'on';
    }

    private function uniqueSlugFrom(string $title, int $sourceId, string $prefix): string
    {
        $slug = Str::slug($title);
        if ($slug === '') {
            $slug = $prefix.'-'.$sourceId;
        }

        $candidate = $slug;
        $suffix = 1;
        while (Product::query()->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.(++$suffix);
        }

        return $candidate;
    }

    private function mapOrderStatus(string $state, string $payment): string
    {
        $state = trim($state);
        if ($state === '') {
            return 'pending';
        }

        if (mb_stripos($state, 'لغو') !== false || mb_stripos($state, 'کنسل') !== false) {
            return 'cancelled';
        }

        if (mb_stripos($state, 'منتظر') !== false) {
            return 'pending';
        }

        // Treat delivered/received statuses from source as paid in our system.
        if (
            mb_stripos($state, 'deliver') !== false ||
            mb_stripos($state, 'تحویل') !== false ||
            mb_stripos($state, 'ارسال') !== false ||
            mb_stripos($state, 'delivered') !== false
        ) {
            return 'paid';
        }

        return 'paid';
    }

    private function grantAccessForOrder(Order $order): void
    {
        foreach ($order->items()->get() as $item) {
            if (! $item->product_id) {
                continue;
            }

            \App\Models\ProductAccess::query()->firstOrCreate([
                'user_id' => $order->user_id,
                'product_id' => $item->product_id,
                'order_item_id' => $item->id,
            ], [
                'granted_at' => now(),
                'expires_at' => null,
                'meta' => ['imported' => true],
            ]);
        }
    }

    private function normalizePaymentGateway(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'unknown';
        }

        return mb_strtolower(str_replace(' ', '-', $value));
    }

    private function mapPaymentStatus(string $state, string $payment): string
    {
        if (mb_stripos($state, 'منتظر') !== false) {
            return 'pending';
        }

        if (mb_stripos($payment, 'آنلاین') !== false || mb_stripos($payment, 'online') !== false) {
            return 'paid';
        }

        return 'pending';
    }

    private function printReport(): void
    {
        $this->command->info('CSV import report:');
        $this->command->info(sprintf('  Admins created: %d, skipped: %d', $this->report['admins']['created'], $this->report['admins']['skipped']));
        $this->command->info(sprintf('  Customers imported: %d, skipped: %d', $this->report['customers']['imported'], $this->report['customers']['skipped']));
        $this->command->info(sprintf('  Products imported: %d, updated: %d, skipped: %d', $this->report['products']['imported'], $this->report['products']['updated'], $this->report['products']['skipped']));
        $this->command->info(sprintf('  Orders imported: %d, errors: %d', $this->report['orders']['imported'], count($this->report['orders']['errors'])));
        $this->command->info(sprintf('  Payments created: %d', $this->report['payments']['imported']));
        $this->command->info(sprintf('  Categories created: %d', $this->report['categories']['created']));

        if ($this->report['customers']['errors']) {
            $this->command->warn('Customer import warnings:');
            foreach ($this->report['customers']['errors'] as $message) {
                $this->command->warn('    '.$message);
            }
        }

        if ($this->report['products']['errors']) {
            $this->command->warn('Product import warnings:');
            foreach ($this->report['products']['errors'] as $message) {
                $this->command->warn('    '.$message);
            }
        }

        if ($this->report['orders']['errors']) {
            $this->command->warn('Order import warnings:');
            foreach ($this->report['orders']['errors'] as $message) {
                $this->command->warn('    '.$message);
            }
        }
    }
}
