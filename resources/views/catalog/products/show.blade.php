@extends('layouts.spa')

@section('title', $product->title)

@section('content')
    <div class="container h-full flex flex-col justify-center py-6">
        <div class="mb-4">
             <a class="btn btn--ghost btn--sm text-white/70 hover:text-white" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('products.index') }}">
                ← بازگشت
            </a>
        </div>

        <div class="panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden flex flex-col md:flex-row h-full max-h-[80vh]">
            
            <!-- Left Side: Image & Key Info -->
            <div class="w-full md:w-1/3 bg-black/20 p-6 flex flex-col border-l border-white/10">
                @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : null)
                @if ($thumbUrl)
                    <div class="aspect-video rounded-xl bg-cover bg-center mb-6 shadow-lg border border-white/5" 
                         style="background-image: url('{{ $thumbUrl }}')"></div>
                @endif

                <h1 class="h3 mb-2">{{ $product->title }}</h1>
                <div class="text-muted text-sm mb-4">{{ $product->type === 'video' ? 'ویدیو آموزشی' : ($product->type === 'course' ? 'دوره آموزشی' : 'جزوه آموزشی') }}</div>

                <div class="mt-auto">
                    @php($discountLabel = $product->discountLabel())
                    <div class="flex items-center justify-between mb-4">
                        @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                        @php($original = $product->originalPrice())
                        @php($final = $product->finalPrice())
                        
                        @if ($product->hasDiscount())
                            <div class="flex flex-col">
                                <span class="text-sm text-muted line-through">{{ number_format($original) }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-bold text-brand">{{ number_format($final) }} <span class="text-sm">{{ $currencyUnit }}</span></span>
                                    @if ($discountLabel)
                                        <span class="badge badge--danger text-xs">{{ $discountLabel }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <span class="text-2xl font-bold text-brand">{{ number_format($final) }} <span class="text-sm">{{ $currencyUnit }}</span></span>
                        @endif
                    </div>

                    @if (($isPurchased ?? false) && auth()->check())
                        <a class="btn btn--success w-full" href="{{ route('panel.library.show', $product->slug) }}">مشاهده در کتابخانه</a>
                    @else
                        <form method="post" action="{{ route('cart.items.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button class="btn btn--primary w-full" type="submit">افزودن به سبد خرید</button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Right Side: Details & Tabs -->
            <div class="w-full md:w-2/3 p-6 flex-1 overflow-y-auto custom-scrollbar">
                
                <!-- Description -->
                <div class="mb-8">
                    <h2 class="h4 mb-3 border-b border-white/10 pb-2">توضیحات</h2>
                    @if ($product->excerpt)
                        <div class="text-lg mb-4 text-white/90">{{ $product->excerpt }}</div>
                    @endif

                    @if (($product->description ?? '') !== '')
                        <div class="text-white/80 leading-relaxed space-y-4">
                            @foreach (preg_split("/\\n\\s*\\n/", (string) $product->description) as $paragraph)
                                @php($paragraphText = trim((string) $paragraph))
                                @if ($paragraphText !== '')
                                    <p>{{ $paragraphText }}</p>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">توضیحاتی برای این محصول ثبت نشده است.</p>
                    @endif
                </div>

                <!-- Preview Video -->
                @if ($product->type === 'video' && $product->video?->preview_media_id)
                    <div class="mb-8">
                        <h2 class="h4 mb-3 border-b border-white/10 pb-2">پیش‌نمایش</h2>
                        <video class="w-full rounded-xl border border-white/10" controls preload="metadata">
                            <source src="{{ route('products.preview', $product->slug) }}" type="video/mp4">
                        </video>
                    </div>
                @endif

                <!-- Reviews & Ratings -->
                <div>
                    <h2 class="h4 mb-3 border-b border-white/10 pb-2">نظرات کاربران</h2>
                    
                    @if (($ratingsArePublic ?? false) && isset($avgRating) && $avgRating !== null)
                        <div class="flex items-center gap-4 mb-6 bg-white/5 p-4 rounded-lg">
                            <div class="text-3xl font-bold text-brand">{{ number_format((float) $avgRating, 1) }}</div>
                            <div class="flex flex-col">
                                <div class="text-yellow-400 text-lg tracking-widest">
                                    @php($filledStars = (int) round((float) $avgRating))
                                    @for ($i = 1; $i <= 5; $i++)
                                        {{ $i <= $filledStars ? '★' : '☆' }}
                                    @endfor
                                </div>
                                <div class="text-xs text-muted">از {{ (int) ($ratingCount ?? 0) }} رأی</div>
                            </div>
                        </div>
                    @endif

                    @auth
                        @if (($isPurchased ?? false) && ! ($userReview ?? null))
                            <form method="post" action="{{ route('products.reviews.store', $product->slug) }}" class="mb-8 p-4 bg-white/5 rounded-lg border border-white/10">
                                @csrf
                                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                <h3 class="h5 mb-3">ثبت نظر</h3>
                                
                                <div class="field mb-3">
                                    <label class="field__label">امتیاز</label>
                                    <select name="rating" class="field__input bg-black/20">
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}">{{ $i }} ستاره</option>
                                        @endfor
                                    </select>
                                </div>
                                
                                <div class="field mb-3">
                                    <label class="field__label">نظر شما</label>
                                    <textarea name="body" class="field__input bg-black/20 h-24"></textarea>
                                </div>
                                
                                <button class="btn btn--primary btn--sm" type="submit">ارسال نظر</button>
                            </form>
                        @elseif (($userReview ?? null))
                            <div class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg mb-6 text-yellow-200 text-sm">
                                وضعیت نظر شما: {{ (string) ($userReview->status ?? '') === 'approved' ? 'تایید شده' : ((string) ($userReview->status ?? '') === 'rejected' ? 'رد شده' : 'در انتظار بررسی') }}
                            </div>
                        @endif
                    @endauth

                    <!-- Reviews list could be added here if available in view data -->
                </div>
            </div>
        </div>
    </div>
@endsection
