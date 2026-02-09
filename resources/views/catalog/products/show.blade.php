@extends('layouts.app')

@section('title', $product->title)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $product->title }}</h1>
            <p class="page-subtitle">{{ $product->type === 'video' ? 'ویدیو آموزشی' : 'جزوه آموزشی' }}</p>

            <div class="panel">
                <div class="stack stack--sm">
                    @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : null)
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" style="width: 100%; max-height: 360px; object-fit: cover; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);" loading="lazy">
                    @endif

                    @if ($product->type === 'video' && ($product->video?->preview_media_id))
                        <div class="field__label">پیش‌نمایش ویدیو</div>
                        <video controls preload="metadata" style="width: 100%; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                            <source src="{{ route('products.preview', $product->slug) }}" type="video/mp4">
                        </video>
                    @endif

                    @if (($ratingsArePublic ?? false) && isset($avgRating) && $avgRating !== null)
                        @php($filledStars = (int) round((float) $avgRating))
                        <div class="field__label">امتیاز کاربران</div>
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <div aria-label="امتیاز {{ number_format((float) $avgRating, 1) }} از ۵" style="letter-spacing: 2px;">
                                @for ($i = 1; $i <= 5; $i++)
                                    {{ $i <= $filledStars ? '★' : '☆' }}
                                @endfor
                            </div>
                            <div>{{ number_format((float) $avgRating, 1) }} از ۵ ({{ (int) ($ratingCount ?? 0) }} رأی)</div>
                        </div>
                    @endif

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
                                        <span class="badge badge--brand">{{ $discountLabel }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="price">{{ number_format($final) }}</span>
                            <span class="price__unit">{{ $currencyUnit }}</span>
                        @endif
                    </div>

                    @if ($product->excerpt)
                        <div class="card__meta">{{ $product->excerpt }}</div>
                    @endif

                    @if ($product->description)
                        <div>{{ $product->description }}</div>
                    @endif

                    @auth
                        @if (($isPurchased ?? false))
                            <div class="panel" style="background: rgba(12, 180, 120, 0.06); border-color: rgba(12, 180, 120, 0.25);">
                                <div class="field__label">این محصول قبلاً خریداری شده است.</div>
                            </div>
                        @endif
                    @endauth

                    @auth
                        @if (($userReview ?? null) && (string) ($userReview->status ?? '') === 'pending')
                            <div class="panel" style="background: rgba(255, 193, 7, 0.06); border-color: rgba(255, 193, 7, 0.25);">
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

                    @auth
                        @if (! $userReview)
                            <div style="height: 1px; background: var(--border); margin: 8px 0;"></div>

                            <div class="field__label">ثبت نظر و امتیاز</div>

                            @if (($isPurchased ?? false))
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
                            @else
                                <div class="field__hint">برای ثبت نظر ابتدا محصول را خریداری کنید.</div>
                            @endif
                        @endif
                    @endauth

                    @if (($reviewsArePublic ?? false))
                        <div style="height: 1px; background: var(--border); margin: 8px 0;"></div>

                        <div class="field__label">نظرات کاربران</div>
                        @if (($reviews ?? collect())->isNotEmpty())
                            <div class="stack stack--sm">
                                @foreach ($reviews as $review)
                                    <div class="panel">
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
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
