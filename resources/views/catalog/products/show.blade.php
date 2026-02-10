@extends('layouts.app')

@section('title', $product->title)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $product->title }}</h1>
            <p class="page-subtitle">{{ $product->type === 'video' ? 'ویدیو آموزشی' : 'جزوه آموزشی' }}</p>

            <div class="stack" style="margin-top: 18px;">
                <div class="panel">
                    <div class="stack stack--sm">
                        @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : null)
                        @if ($thumbUrl)
                            <img class="product-detail__cover" src="{{ $thumbUrl }}" alt="" loading="lazy"
                                onerror="this.onerror=null;this.style.display='none';">
                        @endif

                        @if (($ratingsArePublic ?? false) && isset($avgRating) && $avgRating !== null)
                            @php($filledStars = (int) round((float) $avgRating))
                            <div>
                                <div class="field__label">امتیاز کاربران</div>
                                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                    <div aria-label="امتیاز {{ number_format((float) $avgRating, 1) }} از ۵" style="letter-spacing: 2px;">
                                        @for ($i = 1; $i <= 5; $i++)
                                            {{ $i <= $filledStars ? '★' : '☆' }}
                                        @endfor
                                    </div>
                                    <div>{{ number_format((float) $avgRating, 1) }} از ۵ ({{ (int) ($ratingCount ?? 0) }} رأی)</div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <div class="section__title section__title--sm">قیمت</div>
                            @php($discountLabel = $product->discountLabel())
                            <div class="card__price">
                                @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                @php($original = $product->originalPrice())
                                @php($final = $product->finalPrice())
                                @if ($product->hasDiscount())
                                    <div class="card__price--stack">
                                        <div class="card__price">
                                            <span class="price price--old">{{ number_format($original) }}</span>
                                            <span class="price__unit price__unit--old">{{ $currencyUnit }}</span>
                                        </div>
                                        <div class="card__price">
                                            <span class="price">{{ number_format($final) }}</span>
                                            <span class="price__unit">{{ $currencyUnit }}</span>
                                        </div>
                                        @if ($discountLabel)
                                            <div>
                                                <span class="badge badge--danger">{{ $discountLabel }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="price">{{ number_format($final) }}</span>
                                    <span class="price__unit">{{ $currencyUnit }}</span>
                                @endif
                            </div>
                        </div>

                        @auth
                            @if (($isPurchased ?? false))
                                <div class="panel panel--success">
                                    <div class="field__label">این محصول قبلاً خریداری شده است.</div>
                                </div>
                            @endif
                        @endauth

                        @auth
                            @if (($userReview ?? null) && (string) ($userReview->status ?? '') === 'pending')
                                <div class="panel panel--warning">
                                    <div class="stack stack--sm">
                                        <div class="field__label">در انتظار بررسی</div>
                                        @if ($userReview->body)
                                            <div>{{ $userReview->body }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endauth

                        <div class="form-actions">
                            @if (($isPurchased ?? false) && auth()->check())
                                <a class="btn btn--primary" href="{{ route('panel.library.show', $product->slug) }}">مشاهده در کتابخانه</a>
                            @else
                                <form method="post" action="{{ route('cart.items.store') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button class="btn btn--primary" type="submit">افزودن به سبد</button>
                                </form>
                            @endif
                            <a class="btn btn--ghost" href="{{ route('products.index') }}">بازگشت</a>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="section__title section__title--sm">توضیحات</div>
                        @if ($product->excerpt)
                            <div class="card__meta">{{ $product->excerpt }}</div>
                        @endif

                        @if (($product->description ?? '') !== '')
                            <div class="stack stack--sm">
                                @foreach (preg_split("/\\n\\s*\\n/", (string) $product->description) as $paragraph)
                                    @php($paragraphText = trim((string) $paragraph))
                                    @if ($paragraphText !== '')
                                        <p style="margin: 0;">{{ $paragraphText }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @elseif (! $product->excerpt)
                            <div class="card__meta">توضیحاتی برای این محصول ثبت نشده است.</div>
                        @endif
                    </div>
                </div>

                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="section__title section__title--sm">محتوای ویدیو</div>

                        @if ($product->type !== 'video')
                            <div class="card__meta">این بخش فقط برای محصولات ویدیویی است.</div>
                        @else
                            @if ($product->video?->preview_media_id)
                                <div class="field__label">پیش‌نمایش</div>
                                <video class="product-detail__video" controls preload="metadata">
                                    <source src="{{ route('products.preview', $product->slug) }}" type="video/mp4">
                                </video>
                            @else
                                <div class="card__meta">پیش‌نمایشی برای این ویدیو ثبت نشده است.</div>
                            @endif

                            @if ($product->video?->media_id)
                                @if (($isPurchased ?? false) && auth()->check())
                                    <div class="form-actions">
                                        <a class="btn btn--primary" href="{{ route('panel.library.show', $product->slug) }}">مشاهده ویدیو</a>
                                    </div>
                                @else
                                    <div class="card__meta">محتوای کامل ویدیو پس از خرید داخل سایت قابل مشاهده است.</div>
                                @endif
                            @else
                                <div class="card__meta">ویدیوی اصلی هنوز آپلود نشده است.</div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="section__title section__title--sm">فایل PDF</div>

                        @if ($product->type !== 'note')
                            <div class="card__meta">این بخش فقط برای جزوه‌ها است.</div>
                        @else
                            @php($downloadPart = ($product->parts ?? collect())->first(fn ($part) => (int) ($part->media_id ?? 0) > 0))
                            @if ($downloadPart)
                                @if (($isPurchased ?? false) && auth()->check())
                                    <div class="form-actions">
                                        <a class="btn btn--primary" href="{{ route('panel.library.parts.stream', ['product' => $product->slug, 'part' => $downloadPart->id]) }}">دانلود PDF</a>
                                    </div>
                                @elseif (auth()->check())
                                    <div class="card__meta">برای دانلود فایل، ابتدا محصول را خریداری کنید.</div>
                                @else
                                    <div class="form-actions">
                                        <a class="btn btn--ghost" href="{{ route('login') }}">ورود برای دانلود</a>
                                    </div>
                                @endif
                            @else
                                <div class="card__meta">برای این جزوه هنوز فایل PDF ثبت نشده است.</div>
                            @endif
                        @endif
                    </div>
                </div>

                @auth
                    @if (($isPurchased ?? false) && ! $userReview)
                        <div class="panel">
                            <div class="stack stack--sm">
                                <div class="section__title section__title--sm">ثبت نظر و امتیاز</div>

                                <form method="post" action="{{ route('products.reviews.store', $product->slug) }}" class="stack stack--sm">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                                    <label class="field">
                                        <span class="field__label">امتیاز (۱ تا ۵)</span>
                                        <select name="rating" required>
                                            @for ($i = 5; $i >= 1; $i--)
                                                <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }}</option>
                                            @endfor
                                        </select>
                                        @error('rating')
                                            <div class="field__error">{{ $message }}</div>
                                        @enderror
                                    </label>

                                    <label class="field">
                                        <span class="field__label">نظر (اختیاری)</span>
                                        <textarea name="body" rows="4">{{ old('body', '') }}</textarea>
                                        @error('body')
                                            <div class="field__error">{{ $message }}</div>
                                        @enderror
                                    </label>

                                    <div class="form-actions">
                                        <button class="btn btn--primary" type="submit">ثبت</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endauth

                @if (($reviewsArePublic ?? false))
                    <div class="panel">
                        <div class="stack stack--sm">
                            <div class="section__title section__title--sm">نظرات کاربران</div>
                            @if (($reviews ?? collect())->isNotEmpty())
                                <div class="stack stack--sm">
                                    @foreach ($reviews as $review)
                                        <div class="panel panel--soft">
                                            @php($name = (string) (($review->user->name ?? '') ?: 'کاربر'))
                                            <div class="cluster" style="justify-content: space-between;">
                                                <div>{{ $review->user_id === auth()->id() ? 'شما' : $name }}</div>
                                                <div aria-label="امتیاز {{ (int) $review->rating }} از ۵" style="letter-spacing: 2px;">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        {{ $i <= (int) $review->rating ? '★' : '☆' }}
                                                    @endfor
                                                </div>
                                            </div>
                                            @if ($review->body)
                                                <div style="margin-top: 8px;">{{ $review->body }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="field__hint">
                                    @if ($product->type === 'video')
                                        اولین نفری باشید که این ویدیو را بررسی می‌کند.
                                    @else
                                        اولین نفری باشید که این جزوه را بررسی می‌کند.
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
