<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Post;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $usersCount = User::query()->count();
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

        return view('admin.dashboard', [
            'usersCount' => $usersCount,
            'paidOrdersCount' => $paidOrdersCount,
            'totalSales' => $totalSales,
            'openTicketsCount' => $openTicketsCount,
            'publishedPostsCount' => $publishedPostsCount,
            'salesSeries' => $salesSeries,
        ]);
    }
}
