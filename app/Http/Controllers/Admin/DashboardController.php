<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $usersCount = User::query()->count();
        $productsCount = Product::query()->count();
        $paidOrdersCount = Order::query()->where('status', 'paid')->count();
        $totalSales = (int) Order::query()->where('status', 'paid')->sum('payable_amount');
        $openTicketsCount = Ticket::query()->where('status', 'open')->count();
        $publishedPostsCount = Post::query()->where('status', 'published')->count();

        $days = collect(range(6, 0))
            ->map(fn (int $daysAgo) => now()->subDays($daysAgo)->startOfDay());

        $from = $days->first();
        $to = $days->last()?->copy()->endOfDay();

        $paidOrders = Order::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->get(['paid_at', 'payable_amount']);

        $salesByDay = $paidOrders
            ->groupBy(fn (Order $order) => $order->paid_at?->toDateString() ?? now()->toDateString())
            ->map(fn ($orders) => (int) $orders->sum(fn (Order $order) => (int) ($order->payable_amount ?? 0)));

        $salesSeries = $days->map(function ($day) use ($salesByDay) {
            $key = $day->toDateString();

            return [
                'date' => $key,
                'total' => (int) ($salesByDay[$key] ?? 0),
            ];
        })->values();

        $sessionAnalytics = $this->sessionAnalytics();

        return view('admin.dashboard', [
            'usersCount' => $usersCount,
            'productsCount' => $productsCount,
            'paidOrdersCount' => $paidOrdersCount,
            'totalSales' => $totalSales,
            'openTicketsCount' => $openTicketsCount,
            'publishedPostsCount' => $publishedPostsCount,
            'salesSeries' => $salesSeries,
            'sessionAnalytics' => $sessionAnalytics,
        ]);
    }

    private function sessionAnalytics(): array
    {
        if (! Schema::hasTable('sessions')) {
            return [
                'days' => 7,
                'sessions' => 0,
                'device' => [],
                'countries' => [],
            ];
        }

        $since = now()->subDays(7)->timestamp;

        $rows = DB::table('sessions')
            ->where('last_activity', '>=', $since)
            ->orderByDesc('last_activity')
            ->limit(1500)
            ->get(['id', 'user_id', 'ip_address', 'user_agent', 'payload', 'last_activity']);

        $deviceCounts = [
            'mobile' => 0,
            'web' => 0,
        ];
        $countryCounts = [];
        $total = 0;

        foreach ($rows as $row) {
            $total += 1;
            $userAgent = is_string($row->user_agent ?? null) ? (string) $row->user_agent : '';
            $payload = is_string($row->payload ?? null) ? (string) $row->payload : '';

            $data = $this->decodeSessionPayload($payload);

            $country = $this->normalizeCountryCode(
                data_get($data, 'analytics.country')
                    ?? data_get($data, 'country_code')
                    ?? data_get($data, 'country')
            );

            if ($country === null) {
                $country = 'UNK';
            }

            $device = (string) (data_get($data, 'analytics.device') ?? '');
            $device = in_array($device, ['mobile', 'web'], true) ? $device : ($this->isMobileUserAgent($userAgent) ? 'mobile' : 'web');

            $deviceCounts[$device] = (int) ($deviceCounts[$device] ?? 0) + 1;
            $countryCounts[$country] = (int) ($countryCounts[$country] ?? 0) + 1;
        }

        $deviceRows = collect($deviceCounts)
            ->map(function (int $count, string $key) use ($total) {
                $pct = $total > 0 ? (int) round(($count / $total) * 100) : 0;

                return [
                    'key' => $key,
                    'count' => $count,
                    'pct' => $pct,
                ];
            })
            ->values()
            ->all();

        $countryRows = collect($countryCounts)
            ->sortDesc()
            ->map(function (int $count, string $code) use ($total) {
                $pct = $total > 0 ? (int) round(($count / $total) * 100) : 0;

                return [
                    'code' => $code,
                    'count' => $count,
                    'pct' => $pct,
                ];
            })
            ->values()
            ->take(8)
            ->all();

        return [
            'days' => 7,
            'sessions' => $total,
            'device' => $deviceRows,
            'countries' => $countryRows,
        ];
    }

    private function decodeSessionPayload(string $payload): array
    {
        if ($payload === '') {
            return [];
        }

        $decoded = base64_decode($payload, true);
        if (! is_string($decoded) || $decoded === '') {
            return [];
        }

        try {
            $value = unserialize($decoded, ['allowed_classes' => false]);
        } catch (\Throwable) {
            return [];
        }

        return is_array($value) ? $value : [];
    }

    private function isMobileUserAgent(string $userAgent): bool
    {
        if ($userAgent === '') {
            return false;
        }

        return (bool) preg_match('/android|iphone|ipad|ipod|mobile|iemobile|opera mini|blackberry|webos/i', $userAgent);
    }

    private function normalizeCountryCode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $code = strtoupper(trim($value));
        if (! preg_match('/^[A-Z]{2}$/', $code)) {
            return null;
        }

        return $code;
    }
}
