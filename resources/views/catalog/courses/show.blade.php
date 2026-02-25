@extends('layouts.spa')

@section('title', $course->title)

@section('content')
    @php($placeholderThumb = asset('images/default_image.webp'))
    @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : $placeholderThumb)
    @php($institutionTitle = $course->institutionCategory?->title)
    @php($categoryTitle = ($course->categories ?? collect())->firstWhere('type', 'video')?->title ?? ($course->categories ?? collect())->first()?->title)
    @php($publishedAtLabel = $course->published_at ? jdate($course->published_at)->format('Y/m/d') : null)
    @php($totalVideosCount = (int) ($course->course?->total_videos_count ?? 0))
    @php($totalDurationSeconds = (int) ($course->course?->total_duration_seconds ?? 0))
    @php($totalDurationMinutes = $totalDurationSeconds > 0 ? (int) floor($totalDurationSeconds / 60) : 0)
    @php($totalDurationRemainderSeconds = $totalDurationSeconds > 0 ? $totalDurationSeconds % 60 : 0)
    @php($backCategory = ($course->categories ?? collect())->firstWhere('type', 'video') ?: ($course->categories ?? collect())->firstWhere('type', 'course') ?: ($course->categories ?? collect())->first())
    @php($backParams = $backCategory?->slug ? ['type' => 'video', 'category' => $backCategory->slug] : ['type' => 'video'])
    @php($backUrl = route('products.index', $backParams))

    <div class="detail-shell">
        <div class="detail-header">
            <a class="btn btn--ghost btn--sm text-white/70 hover:text-white" href="{{ $backUrl }}">← بازگشت</a>
            <span class="badge badge--brand">دوره</span>
        </div>

        <div class="detail-grid">
            <div class="detail-col">
                <div class="detail-card panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 bg-black/20 border-b border-white/10">
                        <div class="spa-cover shadow-lg border border-white/5">
                            <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                        </div>
                    </div>

                    <div class="p-6 detail-scroll">
                        <h1 class="h3 mb-2">{{ $course->title }}</h1>

                        @if ($course->excerpt)
                            <div class="text-white/80">{{ $course->excerpt }}</div>
                        @endif
                    </div>

                    <div class="p-6 border-t border-white/10">
                        @php($discountLabel = $course->discountLabel())
                        <div class="flex items-center justify-between mb-4">
                        @php($currencyCode = strtoupper((string) ($commerceCurrency ?? 'IRR')))
                        @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')
                        @php($original = $course->displayOriginalPrice($currencyCode))
                        @php($final = $course->displayFinalPrice($currencyCode))
                        @php($discountLabel = $course->discountLabelFor($currencyCode))

                            @if ($course->hasDiscount())
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
                            <a class="btn btn--success w-full" href="{{ route('panel.library.show', $course->slug) }}">مشاهده در کتابخانه</a>
                        @else
                            <form method="post" action="{{ route('cart.items.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $course->id }}">
                                <button class="btn btn--primary w-full" type="submit">افزودن به سبد خرید</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-card panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/10 bg-black/10">
                        <div class="h4">سرفصل‌های دوره</div>
                    </div>

                    <div class="p-6 detail-scroll">
                        @php($sections = $course->course?->sections ?? collect())

                        @if ($sections->isEmpty())
                            <p class="text-muted">برای این دوره هنوز محتوایی ثبت نشده است.</p>
                        @else
                            <div class="stack stack--sm">
                                @foreach ($sections as $section)
                                    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                                        <div class="p-4 bg-white/5 font-bold text-lg flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                            {{ $section->title }}
                                        </div>
                                        <div class="p-0">
                                            @php($lessons = $section->lessons ?? collect())
                                            @if ($lessons->isEmpty())
                                                <div class="p-4 text-muted text-sm">درسی برای این فصل ثبت نشده است.</div>
                                            @else
                                                @foreach ($lessons as $lesson)
                                                    <div class="p-3 border-t border-white/5 flex items-center justify-between hover:bg-white/5 transition-colors">
                                                        <div class="flex items-center gap-3 min-w-0">
                                                            <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-xs text-muted">{{ $loop->iteration }}</span>
                                                            <span class="truncate">{{ $lesson->title }}</span>
                                                        </div>
                                                        @if (($isPurchased ?? false))
                                                            <span class="text-green-400 text-xs">آزاد</span>
                                                        @elseif ($lesson->is_preview)
                                                            <div class="flex items-center gap-3">
                                                                <button type="button" class="btn btn--ghost btn--sm"
                                                                    data-video-modal-url="{{ route('courses.lessons.preview', ['slug' => $course->slug, 'lesson' => $lesson->id]) }}"
                                                                    data-video-modal-title="{{ $course->title }} - {{ $lesson->title }}">مشاهده</button>
                                                                <span class="text-brand text-xs">پیش‌نمایش</span>
                                                            </div>
                                                        @else
                                                            <div class="flex items-center gap-2 text-muted text-xs">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                                                <span>قفل</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-card panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/10 bg-black/10">
                        <div class="h4">جزئیات</div>
                    </div>

                    <div class="p-6 detail-scroll">
                        @if (($course->description ?? '') !== '')
                            <div class="text-white/80 leading-relaxed space-y-4 mb-8">
                                @foreach (preg_split("/\\n\\s*\\n/", (string) $course->description) as $paragraph)
                                    @php($paragraphText = trim((string) $paragraph))
                                    @if ($paragraphText !== '')
                                        <p>{{ $paragraphText }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if ($institutionTitle || $categoryTitle || $publishedAtLabel || $totalVideosCount > 0 || $totalDurationSeconds > 0)
                            <div class="stack stack--xs text-sm text-white/80">
                                @if ($institutionTitle)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-muted">دانشگاه</div>
                                        <div class="font-bold text-white/90">{{ $institutionTitle }}</div>
                                    </div>
                                @endif

                                @if ($categoryTitle)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-muted">دسته‌بندی</div>
                                        <div class="font-bold text-white/90">{{ $categoryTitle }}</div>
                                    </div>
                                @endif

                                @if ($publishedAtLabel)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-muted">انتشار</div>
                                        <div class="font-bold text-white/90">{{ $publishedAtLabel }}</div>
                                    </div>
                                @endif

                                @if ($totalVideosCount > 0)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-muted">تعداد ویدیوها</div>
                                        <div class="font-bold text-white/90">{{ number_format($totalVideosCount) }}</div>
                                    </div>
                                @endif

                                @if ($totalDurationSeconds > 0)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-muted">زمان کل</div>
                                        <div class="font-bold text-white/90">{{ $totalDurationMinutes }}:{{ str_pad((string) $totalDurationRemainderSeconds, 2, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (($course->description ?? '') === '' && ! $course->excerpt)
                            <p class="text-muted mt-6">توضیحاتی برای این دوره ثبت نشده است.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
