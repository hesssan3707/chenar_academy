@extends('layouts.app')

@section('title', $course->title)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $course->title }}</h1>
            <p class="page-subtitle">دوره آموزشی</p>

            <div class="panel">
                <div class="product-detail">
                    <div class="product-detail__media">
                        @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : null)
                        @if ($thumbUrl)
                            <img class="product-detail__cover" src="{{ $thumbUrl }}" alt="" loading="lazy"
                                onerror="this.onerror=null;this.style.display='none';">
                        @endif
                    </div>

                    <div class="product-detail__info">
                        <div class="card__price">
                            @php($currencyUnit = (($course->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($course->currency ?? 'IRR'))
                            @php($discountLabel = $course->discountLabel())
                            @php($original = $course->originalPrice())
                            @php($final = $course->finalPrice())
                            @if ($course->hasDiscount())
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

                        @if ($course->excerpt)
                            <div class="card__meta">{{ $course->excerpt }}</div>
                        @endif

                        @if (($course->description ?? '') !== '')
                            <div class="stack stack--sm">
                                @foreach (preg_split("/\\n\\s*\\n/", (string) $course->description) as $paragraph)
                                    @php($paragraphText = trim((string) $paragraph))
                                    @if ($paragraphText !== '')
                                        <p style="margin: 0;">{{ $paragraphText }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @auth
                            @if (($isPurchased ?? false))
                                <div class="panel panel--success">
                                    <div class="field__label">این دوره قبلاً خریداری شده است.</div>
                                </div>
                            @endif
                        @endauth

                        <div class="form-actions">
                            @if (($isPurchased ?? false) && auth()->check())
                                <a class="btn btn--primary" href="{{ route('panel.library.show', $course->slug) }}">مشاهده در کتابخانه</a>
                            @else
                                <form method="post" action="{{ route('cart.items.store') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $course->id }}">
                                    <button class="btn btn--primary" type="submit">افزودن به سبد</button>
                                </form>
                            @endif
                            <a class="btn btn--ghost" href="{{ route('courses.index') }}">بازگشت</a>
                        </div>
                    </div>
                </div>

                <div style="height: 1px; background: var(--border); margin: 12px 0;"></div>

                <div class="field__label">سرفصل‌های دوره</div>
                @php($sections = $course->course?->sections ?? collect())
                @if ($sections->isEmpty())
                    <div class="card__meta">برای این دوره هنوز محتوایی ثبت نشده است.</div>
                @else
                    <div class="stack stack--sm" style="margin-top: 12px;">
                        @foreach ($sections as $section)
                            <div class="panel panel--soft">
                                <div class="stack stack--sm">
                                    <div class="section__title section__title--sm">{{ $section->title }}</div>

                                    @php($lessons = $section->lessons ?? collect())
                                    @if ($lessons->isEmpty())
                                        <div class="card__meta">هنوز درسی برای این بخش ثبت نشده است.</div>
                                    @else
                                        <div class="stack stack--sm">
                                            @foreach ($lessons as $lesson)
                                                @php($isPreview = (bool) ($lesson->is_preview ?? false))
                                                @php($isLocked = ! ($isPurchased ?? false) && ! $isPreview)

                                                <div class="panel panel--soft">
                                                    <div class="cluster" style="justify-content: space-between;">
                                                        <div class="field__label">{{ $lesson->title }}</div>
                                                        @if ($isPreview)
                                                            <span class="badge badge--brand">پیش‌نمایش</span>
                                                        @elseif ($isLocked)
                                                            <span class="badge">قفل</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
