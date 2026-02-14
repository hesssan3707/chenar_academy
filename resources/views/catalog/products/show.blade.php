@extends('layouts.spa')

@section('title', $product->title)

@section('content')
    @php($placeholderThumb = asset('images/default_image.webp'))
    @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
    @php($hasIntroVideo = ($product->type ?? null) === 'video' && (int) ($product->video?->preview_media_id ?? 0) > 0)
    @php($institutionTitle = $product->institutionCategory?->title)
    @php($categoryTitle = ($product->categories ?? collect())->firstWhere('type', (string) ($product->type ?? ''))?->title ?? ($product->categories ?? collect())->first()?->title)
    @php($publishedAtLabel = $product->published_at ? jdate($product->published_at)->format('Y/m/d') : null)
    @php($durationSeconds = (int) ($product->video?->duration_seconds ?? 0))
    @php($durationMinutes = $durationSeconds > 0 ? (int) floor($durationSeconds / 60) : 0)
    @php($durationRemainderSeconds = $durationSeconds > 0 ? $durationSeconds % 60 : 0)
    @php($hasFullVideo = ($product->type ?? null) === 'video' && (int) ($product->video?->media_id ?? 0) > 0)
    @php($hasBookletFile = ($product->type ?? null) === 'note' && ($product->parts ?? collect())->contains(fn ($part) => (string) ($part->part_type ?? '') === 'file' && (int) ($part->media_id ?? 0) > 0))
    @php($bookletFilePart = ($product->type ?? null) === 'note' ? ($product->parts ?? collect())->first(fn ($part) => (string) ($part->part_type ?? '') === 'file' && (int) ($part->media_id ?? 0) > 0) : null)
    @php($bookletFileStreamUrl = $bookletFilePart && ($isPurchased ?? false) && auth()->check() ? route('panel.library.parts.stream', ['product' => $product->slug, 'part' => $bookletFilePart->id]) : null)
    @php($productTypeLabel = ($product->type ?? null) === 'video' ? 'ویدیو آموزشی' : (($product->type ?? null) === 'course' ? 'دوره آموزشی' : 'جزوه آموزشی'))
    @php($listingType = ($product->type ?? null) === 'note' ? 'note' : 'video')
    @php($backCategory = ($product->categories ?? collect())->firstWhere('type', (string) ($product->type ?? '')) ?: ($product->categories ?? collect())->first())
    @php($backParams = $backCategory?->slug ? ['type' => $listingType, 'category' => $backCategory->slug] : ['type' => $listingType])
    @php($backUrl = route('products.index', $backParams))
    @php($showFeedbackCol = in_array((string) ($product->type ?? ''), ['video', 'note'], true) && (($ratingsArePublic ?? false) || ($reviewsArePublic ?? false)))

    <div class="detail-shell">
        <div class="detail-header">
            <a class="btn btn--ghost btn--sm text-white/70 hover:text-white" href="{{ $backUrl }}">← بازگشت</a>
            <div class="text-muted text-sm">{{ $productTypeLabel }}</div>
        </div>

        <div class="detail-tabs" data-detail-tabs role="tablist" aria-label="بخش‌ها">
            <button type="button" class="detail-tab is-active" data-detail-tab="info" role="tab" aria-selected="true">اطلاعات و خرید</button>
            <button type="button" class="detail-tab" data-detail-tab="content" role="tab" aria-selected="false" tabindex="-1">محتوا</button>
            @if ($showFeedbackCol)
                <button type="button" class="detail-tab" data-detail-tab="feedback" role="tab" aria-selected="false" tabindex="-1">نظرات و امتیاز</button>
            @endif
        </div>

        <div class="detail-grid">
            <div class="detail-col" data-detail-panel="info" role="tabpanel">
                <div class="detail-card panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 bg-black/20 border-b border-white/10">
                        <div class="spa-cover shadow-lg border border-white/5">
                            @if ($hasIntroVideo)
                                <video class="w-full rounded-xl border border-white/10" controls preload="metadata">
                                    <source src="{{ route('products.preview', $product->slug) }}" type="video/mp4">
                                </video>
                            @else
                                <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                            @endif
                        </div>
                    </div>

                    <div class="p-6 detail-scroll">
                        <h1 class="h3 mb-2">{{ $product->title }}</h1>

                        @if ($institutionTitle || $categoryTitle || $publishedAtLabel || (($product->type ?? null) === 'video' && $durationSeconds > 0) || $hasFullVideo || $hasBookletFile)
                            <div class="detail-section">
                                <div class="detail-section__head">
                                    <div class="detail-section__title">اطلاعات</div>
                                </div>
                                <div class="detail-section__body">
                                    <div class="detail-facts">
                                        @if ($institutionTitle)
                                            <div class="detail-fact">
                                                <div class="detail-fact__label">دانشگاه</div>
                                                <div class="detail-fact__value">{{ $institutionTitle }}</div>
                                            </div>
                                        @endif

                                        @if ($categoryTitle)
                                            <div class="detail-fact">
                                                <div class="detail-fact__label">دسته‌بندی</div>
                                                <div class="detail-fact__value">{{ $categoryTitle }}</div>
                                            </div>
                                        @endif

                                        @if ($publishedAtLabel)
                                            <div class="detail-fact">
                                                <div class="detail-fact__label">انتشار</div>
                                                <div class="detail-fact__value">{{ $publishedAtLabel }}</div>
                                            </div>
                                        @endif

                                        @if (($product->type ?? null) === 'video' && $durationSeconds > 0)
                                            <div class="detail-fact">
                                                <div class="detail-fact__label">مدت زمان</div>
                                                <div class="detail-fact__value">{{ $durationMinutes }}:{{ str_pad((string) $durationRemainderSeconds, 2, '0', STR_PAD_LEFT) }}</div>
                                            </div>
                                        @endif

                                        @if ($hasFullVideo)
                                            <div class="detail-fact">
                                                <div class="detail-fact__label">ویدیو کامل</div>
                                                <div class="detail-fact__value">پس از خرید</div>
                                            </div>
                                        @endif

                                        @if ($hasBookletFile)
                                            <div class="detail-fact">
                                                <div class="detail-fact__label">فایل PDF</div>
                                                <div class="detail-fact__value">پس از خرید</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="p-6 border-t border-white/10">
                        @php($currencyCode = strtoupper((string) ($commerceCurrency ?? 'IRR')))
                        @php($displayCurrencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')
                        @php($original = $product->displayOriginalPrice($currencyCode))
                        @php($final = $product->displayFinalPrice($currencyCode))
                        @php($discountLabel = $product->discountLabelFor($currencyCode))

                        <div class="detail-section">
                            <div class="detail-section__head">
                                <div class="detail-section__title">قیمت و خرید</div>
                            </div>
                            <div class="detail-section__body">
                                <div class="p-4 bg-white/5 border border-white/10 rounded-xl mb-4">
                                    @if ($product->hasDiscount())
                                        <div class="flex flex-col">
                                            <span class="text-sm text-muted line-through">{{ number_format($original) }} <span class="text-xs">{{ $displayCurrencyUnit }}</span></span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl font-bold text-brand">{{ number_format($final) }} <span class="text-sm">{{ $displayCurrencyUnit }}</span></span>
                                                @if ($discountLabel)
                                                    <span class="badge badge--danger text-xs">{{ $discountLabel }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-2xl font-bold text-brand">{{ number_format($final) }} <span class="text-sm">{{ $displayCurrencyUnit }}</span></div>
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
                    </div>
                </div>
            </div>

            <div class="detail-col" data-detail-panel="content" role="tabpanel">
                <div class="detail-card panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    @if (($product->type ?? null) !== 'video' && ($product->type ?? null) !== 'note')
                        <div class="p-6 border-b border-white/10 bg-black/10">
                            <div class="h4">توضیحات</div>
                        </div>
                    @endif

                    <div class="p-6 detail-scroll">
                        @if (($product->type ?? null) === 'video')
                            <div class="detail-section">
                                <div class="detail-section__head">
                                    <div class="detail-section__title">ویدیو</div>
                                </div>
                                <div class="detail-section__body">
                                    <div class="locked-video">
                                        <img class="locked-video__media" src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                        <div class="locked-video__overlay">
                                            <div class="locked-video__content">
                                                <div class="locked-video__title">ویدیو قفل است</div>
                                                <div class="locked-video__hint">برای مشاهده ویدیوی کامل، پس از خرید از بخش کتابخانه استفاده کنید.</div>
                                                @if (($isPurchased ?? false) && auth()->check())
                                                    <a class="btn btn--success btn--sm" href="{{ route('panel.library.show', $product->slug) }}">رفتن به کتابخانه</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <div class="detail-section__head">
                                    <div class="detail-section__title">توضیحات</div>
                                </div>
                                <div class="detail-section__body">
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
                                    @elseif (! $product->excerpt)
                                        <p class="text-muted">توضیحاتی برای این ویدیو ثبت نشده است.</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            @if (($product->type ?? null) === 'note')
                                <div class="detail-section">
                                    <div class="detail-section__head">
                                        <div class="detail-section__title">فایل PDF</div>
                                    </div>
                                    <div class="detail-section__body">
                                        @if ($bookletFileStreamUrl)
                                            <iframe src="{{ $bookletFileStreamUrl }}" style="width: 100%; height: 420px; max-height: 60vh; border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; background: rgba(0,0,0,0.2);"></iframe>
                                        @else
                                            <div class="p-4 bg-white/5 border border-white/10 rounded-xl">
                                                <div class="font-bold text-white/90 mb-2">فایل PDF قفل است</div>
                                                <div class="text-sm text-muted">برای مشاهده فایل PDF، پس از خرید از بخش کتابخانه استفاده کنید.</div>
                                                @if (($isPurchased ?? false) && auth()->check())
                                                    <div class="mt-4">
                                                        <a class="btn btn--success btn--sm" href="{{ route('panel.library.show', $product->slug) }}">رفتن به کتابخانه</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="detail-section">
                                    <div class="detail-section__body">
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
                                        @elseif (! $product->excerpt)
                                            <p class="text-muted">توضیحاتی برای این جزوه ثبت نشده است.</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="detail-section">
                                    <div class="detail-section__head">
                                        <div class="detail-section__title">محتوا</div>
                                    </div>
                                    <div class="detail-section__body">
                                        @php($parts = $product->parts ?? collect())

                                        @if ($parts->isEmpty())
                                            <p class="text-muted">برای این جزوه هنوز بخشی ثبت نشده است.</p>
                                        @else
                                            <div class="stack stack--sm">
                                                @foreach ($parts as $part)
                                                    @php($partTitle = trim((string) ($part->title ?? '')))
                                                    @php($partType = (string) ($part->part_type ?? ''))
                                                    <div class="p-4 bg-white/5 border border-white/10 rounded-xl flex items-center justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <div class="font-bold text-white/90 truncate">{{ $partTitle !== '' ? $partTitle : '—' }}</div>
                                                            <div class="text-xs text-muted">
                                                                {{ $partType === 'file' ? 'فایل' : ($partType === 'text' ? 'متن' : 'بخش') }}
                                                            </div>
                                                        </div>
                                                        @if (($isPurchased ?? false) && auth()->check())
                                                            <span class="text-green-400 text-xs">آزاد</span>
                                                        @else
                                                            <div class="flex items-center gap-2 text-muted text-xs">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                                                <span>قفل</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
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
                                @elseif (! $product->excerpt)
                                    <p class="text-muted">توضیحاتی برای این جزوه ثبت نشده است.</p>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if ($showFeedbackCol)
                <div class="detail-col" data-detail-panel="feedback" role="tabpanel">
                    <div class="detail-card panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                        <div class="p-6 detail-scroll">
                            @if (($product->type ?? null) === 'video')
                                @if (($ratingsArePublic ?? false))
                                    <div class="detail-section">
                                        <div class="detail-section__head">
                                            <div class="detail-section__title">امتیاز</div>
                                        </div>
                                        <div class="detail-section__body">
                                            @if (isset($avgRating) && $avgRating !== null)
                                                <div class="flex items-center gap-4 bg-white/5 p-4 rounded-lg border border-white/10">
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
                                            @else
                                                <div class="text-muted">هنوز امتیازی ثبت نشده است.</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if (($reviewsArePublic ?? false))
                                    <div class="detail-section">
                                        <div class="detail-section__head">
                                            <div class="detail-section__title">نظرات</div>
                                        </div>
                                        <div class="detail-section__body">
                                            @auth
                                                @if (($userReview ?? null))
                                                    <div class="detail-section">
                                                        <div class="detail-section__head">
                                                            <div class="detail-section__title">نظر شما</div>
                                                        </div>
                                                        <div class="detail-section__body text-white/80 text-sm">
                                                            <div class="mb-2">
                                                                وضعیت نظر شما: {{ (string) ($userReview->status ?? '') === 'approved' ? 'تایید شده' : ((string) ($userReview->status ?? '') === 'rejected' ? 'رد شده' : 'در انتظار بررسی') }}
                                                            </div>
                                                            @if (($userReview->body ?? '') !== '')
                                                                <div>{{ $userReview->body }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @elseif (($isPurchased ?? false))
                                                    <div class="detail-section">
                                                        <div class="detail-section__head">
                                                            <div class="detail-section__title">ثبت نظر</div>
                                                        </div>
                                                        <div class="detail-section__body">
                                                            <form method="post" action="{{ route('products.reviews.store', $product->slug) }}">
                                                                @csrf
                                                                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

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
                                                        </div>
                                                    </div>
                                                @endif
                                            @endauth

                                            @php($reviews = $reviews ?? collect())
                                            @if ($reviews->isEmpty())
                                                <div class="text-muted">اولین نفری باشید که این ویدیو را بررسی می‌کند.</div>
                                            @else
                                                <div class="detail-section">
                                                    <div class="detail-section__head">
                                                        <div class="detail-section__title">آخرین نظرات</div>
                                                    </div>
                                                    <div class="detail-section__body">
                                                        <div class="stack stack--sm">
                                                            @foreach ($reviews as $review)
                                                                @if (($review->body ?? '') !== '')
                                                                    <div class="p-4 bg-white/5 border border-white/10 rounded-lg text-white/80">
                                                                        <div>{{ $review->body }}</div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                            @if (($product->type ?? null) === 'note')
                                @if (($ratingsArePublic ?? false))
                                    <div class="detail-section">
                                        <div class="detail-section__head">
                                            <div class="detail-section__title">امتیاز</div>
                                        </div>
                                        <div class="detail-section__body">
                                            @if (isset($avgRating) && $avgRating !== null)
                                                <div class="flex items-center gap-4 bg-white/5 p-4 rounded-lg border border-white/10">
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
                                            @else
                                                <div class="text-muted">هنوز امتیازی ثبت نشده است.</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if (($reviewsArePublic ?? false))
                                    <div class="detail-section">
                                        <div class="detail-section__head">
                                            <div class="detail-section__title">نظرات</div>
                                        </div>
                                        <div class="detail-section__body">
                                            @auth
                                                @if (($userReview ?? null))
                                                    <div class="detail-section">
                                                        <div class="detail-section__head">
                                                            <div class="detail-section__title">نظر شما</div>
                                                        </div>
                                                        <div class="detail-section__body text-white/80 text-sm">
                                                            <div class="mb-2">
                                                                وضعیت نظر شما: {{ (string) ($userReview->status ?? '') === 'approved' ? 'تایید شده' : ((string) ($userReview->status ?? '') === 'rejected' ? 'رد شده' : 'در انتظار بررسی') }}
                                                            </div>
                                                            @if (($userReview->body ?? '') !== '')
                                                                <div>{{ $userReview->body }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @elseif (($isPurchased ?? false))
                                                    <div class="detail-section">
                                                        <div class="detail-section__head">
                                                            <div class="detail-section__title">ثبت نظر</div>
                                                        </div>
                                                        <div class="detail-section__body">
                                                            <form method="post" action="{{ route('products.reviews.store', $product->slug) }}">
                                                                @csrf
                                                                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

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
                                                        </div>
                                                    </div>
                                                @endif
                                            @endauth

                                            @php($reviews = $reviews ?? collect())
                                            @if ($reviews->isEmpty())
                                                <div class="text-muted">اولین نفری باشید که این محصول را بررسی می‌کند.</div>
                                            @else
                                                <div class="detail-section">
                                                    <div class="detail-section__head">
                                                        <div class="detail-section__title">آخرین نظرات</div>
                                                    </div>
                                                    <div class="detail-section__body">
                                                        <div class="stack stack--sm">
                                                            @foreach ($reviews as $review)
                                                                @if (($review->body ?? '') !== '')
                                                                    <div class="p-4 bg-white/5 border border-white/10 rounded-lg text-white/80">
                                                                        <div>{{ $review->body }}</div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
