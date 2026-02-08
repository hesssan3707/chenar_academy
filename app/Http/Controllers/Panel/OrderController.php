<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->orderByDesc('placed_at')
            ->orderByDesc('id')
            ->get();

        return view('panel.orders.index', [
            'title' => 'سفارش‌های من',
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, int $order): View
    {
        $orderModel = Order::query()
            ->where('id', $order)
            ->where('user_id', $request->user()->id)
            ->with(['items.product', 'payments'])
            ->firstOrFail();

        return view('panel.orders.show', [
            'title' => 'جزئیات سفارش',
            'order' => $orderModel,
        ]);
    }
}
