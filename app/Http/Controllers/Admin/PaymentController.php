<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::query()->orderByDesc('id')->paginate(40);

        return view('admin.payments.index', [
            'title' => 'پرداخت‌ها',
            'payments' => $payments,
        ]);
    }

    public function create(): View
    {
        return view('admin.payments.form', [
            'title' => 'ایجاد پرداخت',
            'payment' => new Payment([
                'status' => 'initiated',
                'currency' => $this->commerceCurrency(),
                'amount' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.payments.index')->with('toast', [
            'type' => 'warning',
            'title' => 'ثبت پرداخت',
            'message' => 'پرداخت‌ها از طریق فرآیند پرداخت ایجاد می‌شوند.',
        ]);
    }

    public function show(int $payment): View
    {
        $paymentModel = Payment::query()->with('order')->findOrFail($payment);

        return view('admin.payments.show', [
            'title' => 'نمایش پرداخت',
            'payment' => $paymentModel,
        ]);
    }

    public function edit(int $payment): View
    {
        $paymentModel = Payment::query()->findOrFail($payment);

        return view('admin.payments.form', [
            'title' => 'ویرایش پرداخت',
            'payment' => $paymentModel,
        ]);
    }

    public function update(Request $request, int $payment): RedirectResponse
    {
        $paymentModel = Payment::query()->findOrFail($payment);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['initiated', 'paid', 'failed'])],
            'reference_id' => ['nullable', 'string', 'max:100'],
        ]);

        $paymentModel->status = (string) $validated['status'];
        $paymentModel->reference_id = isset($validated['reference_id']) && $validated['reference_id'] !== '' ? (string) $validated['reference_id'] : null;

        if ($paymentModel->status === 'paid') {
            $paymentModel->paid_at = $paymentModel->paid_at ?: now();
        } else {
            $paymentModel->paid_at = null;
        }

        $paymentModel->save();

        return redirect()->route('admin.payments.edit', $paymentModel->id);
    }

    public function destroy(int $payment): RedirectResponse
    {
        return redirect()->route('admin.payments.index')->with('toast', [
            'type' => 'warning',
            'title' => 'حذف پرداخت',
            'message' => 'حذف پرداخت در این نسخه فعال نیست.',
        ]);
    }
}
