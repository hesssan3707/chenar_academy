<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()->orderByDesc('id')->paginate(40);

        return view('admin.orders.index', [
            'title' => 'سفارش‌ها',
            'orders' => $orders,
        ]);
    }

    public function create(): View
    {
        return view('admin.orders.form', [
            'title' => 'ایجاد سفارش',
            'order' => new Order([
                'status' => 'pending',
                'currency' => $this->commerceCurrency(),
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'payable_amount' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.orders.index')->with('toast', [
            'type' => 'warning',
            'title' => 'ثبت سفارش',
            'message' => 'سفارش‌ها از طریق فرآیند پرداخت ایجاد می‌شوند.',
        ]);
    }

    public function show(int $order): View
    {
        $orderModel = Order::query()->with(['items', 'payments'])->findOrFail($order);

        return view('admin.orders.show', [
            'title' => 'نمایش سفارش',
            'order' => $orderModel,
        ]);
    }

    public function edit(int $order): View
    {
        $orderModel = Order::query()->findOrFail($order);

        return view('admin.orders.form', [
            'title' => 'ویرایش سفارش',
            'order' => $orderModel,
        ]);
    }

    public function update(Request $request, int $order): RedirectResponse
    {
        $orderModel = Order::query()->findOrFail($order);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'paid', 'cancelled'])],
        ]);

        $status = (string) $validated['status'];
        $orderModel->status = $status;

        if ($status === 'paid') {
            $orderModel->paid_at = $orderModel->paid_at ?: now();
            $orderModel->cancelled_at = null;
        } elseif ($status === 'cancelled') {
            $orderModel->cancelled_at = $orderModel->cancelled_at ?: now();
            $orderModel->paid_at = null;
        } else {
            $orderModel->paid_at = null;
            $orderModel->cancelled_at = null;
        }

        $orderModel->save();

        return redirect()->route('admin.orders.edit', $orderModel->id);
    }

    public function destroy(int $order): RedirectResponse
    {
        return redirect()->route('admin.orders.index')->with('toast', [
            'type' => 'warning',
            'title' => 'حذف سفارش',
            'message' => 'حذف سفارش در این نسخه فعال نیست.',
        ]);
    }
}
