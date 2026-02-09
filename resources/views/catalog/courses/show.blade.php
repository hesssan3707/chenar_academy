@extends('layouts.app')

@section('title', $course->title)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $course->title }}</h1>
            <p class="page-subtitle">دوره آموزشی</p>

            <div class="panel">
                <div class="stack stack--sm">
                    @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : null)
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" style="width: 100%; max-height: 360px; object-fit: cover; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);" loading="lazy">
                    @endif

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
                                        <span class="badge badge--brand">{{ $discountLabel }}</span>
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

                    @if ($course->description)
                        <div>{{ $course->description }}</div>
                    @endif

                    @auth
                        @if (($isPurchased ?? false))
                            <div class="panel" style="background: rgba(12, 180, 120, 0.06); border-color: rgba(12, 180, 120, 0.25);">
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

                    <div style="height: 1px; background: var(--border); margin: 8px 0;"></div>

                    <div class="field__label">سرفصل‌های دوره</div>
                    @php($sections = $course->course?->sections ?? collect())
                    @if ($sections->isEmpty())
                        <div class="card__meta">برای این دوره هنوز محتوایی ثبت نشده است.</div>
                    @else
                        <div class="stack stack--sm">
                            @foreach ($sections as $section)
                                <div class="panel" style="background: rgba(15,26,46,0.35); border-style: dashed;">
                                    <div class="stack stack--sm">
                                        <div class="section__title" style="font-size: 18px;">{{ $section->title }}</div>

                                        @php($lessons = $section->lessons ?? collect())
                                        @if ($lessons->isEmpty())
                                            <div class="card__meta">هنوز درسی برای این بخش ثبت نشده است.</div>
                                        @else
                                            <div class="stack stack--sm">
                                                @foreach ($lessons as $lesson)
                                                    @php($isPreview = (bool) ($lesson->is_preview ?? false))
                                                    @php($isLocked = ! ($isPurchased ?? false) && ! $isPreview)

                                                    <div class="panel" style="background: rgba(15,26,46,0.35); border-style: dashed;">
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
        </div>
    </section>
@endsection
