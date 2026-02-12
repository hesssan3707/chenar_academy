@extends('layouts.spa')

@section('title', 'پرداخت کارت‌به‌کارت')

@section('content')
    <div class="container container--wide c2c-shell">
        <div class="c2c-head">
            <h1 class="page-title">پرداخت کارت‌به‌کارت</h1>
        </div>

        @php($items = $items ?? collect())

        @if ($items->isEmpty())
            <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700 max-w-md">
                <div class="stack stack--sm">
                    <p class="text-muted">سبد خرید شما خالی است.</p>
                    <div class="form-actions">
                        <a class="btn btn--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                        <a class="btn btn--ghost" href="{{ route('checkout.index') }}">بازگشت</a>
                    </div>
                </div>
            </div>
        @else
            <div class="c2c-grid">
                <div class="panel c2c-panel">
                    <div class="stack stack--sm">
                        <div class="h4">راهنما</div>
                        <div class="text-muted">رسید پرداخت را آپلود کنید تا سفارش برای بررسی ثبت شود.</div>
                    </div>

                    @php($cardToCardCards = $cardToCardCards ?? [])
                    @if (is_array($cardToCardCards) && count($cardToCardCards) > 0)
                        <div class="stack stack--sm">
                            <div class="h4">اطلاعات کارت مقصد</div>
                            <div class="text-muted">لطفاً مبلغ را به یکی از کارت‌های زیر واریز کنید و سپس رسید را آپلود کنید.</div>

                            <div class="stack stack--xs">
                                @foreach ($cardToCardCards as $card)
                                    @php($cardName = trim((string) ($card['name'] ?? '')))
                                    @php($cardNumber = preg_replace('/\\D+/', '', (string) ($card['number'] ?? '')))
                                    @php($cardNumber = is_string($cardNumber) ? $cardNumber : '')
                                    @php($cardNumberFormatted = strlen($cardNumber) === 16 ? implode('-', str_split($cardNumber, 4)) : $cardNumber)

                                    <div class="panel p-3 bg-white/5 rounded-xl border border-white/10">
                                        <div class="flex justify-between items-start gap-3">
                                            <div class="stack stack--xs" style="min-width: 0;">
                                                @if ($cardName !== '')
                                                    <div class="text-muted">{{ $cardName }}</div>
                                                @endif
                                                <div style="font-weight: 900; letter-spacing: 0.6px;" dir="ltr">{{ $cardNumberFormatted }}</div>
                                            </div>
                                            <div class="stack stack--xs" style="align-items: flex-end;">
                                                <button class="btn btn--ghost btn--sm" type="button" data-copy-trigger data-copy-text="{{ $cardNumber }}">کپی</button>
                                                <div class="text-muted text-xs" data-copy-feedback hidden>کپی شد</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="stack stack--sm">
                        <div class="h4">آپلود رسید</div>
                        <form method="post" action="{{ route('checkout.card-to-card.store') }}" enctype="multipart/form-data" class="stack stack--sm">
                            @csrf

                            <label class="field">
                                <span class="field__label">فایل رسید</span>
                                <input type="file" name="receipt" required accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                <div class="field__hint">فرمت‌های مجاز: JPG، PNG یا PDF (حداکثر 5MB)</div>
                                @error('receipt')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <div class="stack stack--sm">
                                <button class="btn btn--primary w-full" type="submit">ثبت برای بررسی</button>
                                <a class="btn btn--ghost w-full" href="{{ route('checkout.index') }}">بازگشت</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="panel c2c-panel c2c-panel--summary">
                    <div class="checkout-panel__head">
                        <div class="h4">فاکتور نهایی</div>
                    </div>

                    <div class="checkout-summary">
                        <div class="checkout-kv">
                            <div class="text-muted">جمع سبد خرید</div>
                            <div>
                                <span class="price" dir="ltr">{{ number_format($subtotal ?? 0) }}</span>
                                <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                            </div>
                        </div>

                        <div class="checkout-kv checkout-kv--discount">
                            <div>تخفیف</div>
                            <div>
                                <span class="price" dir="ltr">{{ number_format($discountAmount ?? 0) }}</span>
                                <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                            </div>
                        </div>

                        @if ((int) ($taxPercent ?? 0) > 0)
                            <div class="checkout-kv">
                                <div class="text-muted">مالیات (<span dir="ltr">{{ (int) ($taxPercent ?? 0) }}٪</span>)</div>
                                <div>
                                    <span class="price" dir="ltr">{{ number_format($taxAmount ?? 0) }}</span>
                                    <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="checkout-divider"></div>

                        <div class="checkout-payable">
                            <div class="checkout-payable__label">مبلغ قابل پرداخت</div>
                            <div class="checkout-payable__value">
                                <span class="price" dir="ltr">{{ number_format($payableAmount ?? 0) }}</span>
                                <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

                <script>
                    (function () {
                        var buttons = document.querySelectorAll('[data-copy-trigger]');
                        if (!buttons || buttons.length === 0) {
                            return;
                        }

                        function copyText(text) {
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                return navigator.clipboard.writeText(text);
                            }

                            return new Promise(function (resolve, reject) {
                                try {
                                    var el = document.createElement('textarea');
                                    el.value = text;
                                    el.setAttribute('readonly', '');
                                    el.style.position = 'fixed';
                                    el.style.top = '0';
                                    el.style.left = '0';
                                    el.style.opacity = '0';
                                    document.body.appendChild(el);
                                    el.select();
                                    var ok = document.execCommand('copy');
                                    document.body.removeChild(el);
                                    ok ? resolve() : reject(new Error('copy_failed'));
                                } catch (e) {
                                    reject(e);
                                }
                            });
                        }

                        buttons.forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                var text = String(btn.getAttribute('data-copy-text') || '').trim();
                                if (!text) {
                                    return;
                                }

                                var container = btn.closest('.panel');
                                var feedback = container ? container.querySelector('[data-copy-feedback]') : null;

                                copyText(text)
                                    .then(function () {
                                        if (feedback) {
                                            feedback.hidden = false;
                                            clearTimeout(feedback._hideTimer);
                                            feedback._hideTimer = setTimeout(function () {
                                                feedback.hidden = true;
                                            }, 1200);
                                        }
                                    })
                                    .catch(function () {
                                        if (feedback) {
                                            feedback.textContent = 'خطا';
                                            feedback.hidden = false;
                                            clearTimeout(feedback._hideTimer);
                                            feedback._hideTimer = setTimeout(function () {
                                                feedback.textContent = 'کپی شد';
                                                feedback.hidden = true;
                                            }, 1200);
                                        }
                                    });
                            });
                        });
                    })();
                </script>
    </div>
@endsection
