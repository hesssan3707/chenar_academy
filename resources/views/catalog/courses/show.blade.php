@extends('layouts.spa')

@section('title', $course->title)

@section('content')
    <div class="container h-full flex flex-col justify-center py-6">
        <div class="mb-4">
             <a class="btn btn--ghost btn--sm text-white/70 hover:text-white" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('courses.index') }}">
                ← بازگشت
            </a>
        </div>

        <div class="panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden flex flex-col md:flex-row h-full max-h-[80vh]">
            
            <!-- Left Side: Image & Key Info -->
            <div class="w-full md:w-1/3 bg-black/20 p-6 flex flex-col border-l border-white/10">
                @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : null)
                @if ($thumbUrl)
                    <div class="aspect-video rounded-xl bg-cover bg-center mb-6 shadow-lg border border-white/5" 
                         style="background-image: url('{{ $thumbUrl }}')"></div>
                @endif

                <h1 class="h3 mb-2">{{ $course->title }}</h1>
                <div class="text-muted text-sm mb-4">دوره آموزشی</div>

                <div class="mt-auto">
                    @php($discountLabel = $course->discountLabel())
                    <div class="flex items-center justify-between mb-4">
                        @php($currencyUnit = (($course->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($course->currency ?? 'IRR'))
                        @php($original = $course->originalPrice())
                        @php($final = $course->finalPrice())
                        
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

            <!-- Right Side: Syllabus & Description -->
            <div class="w-full md:w-2/3 p-6 flex-1 overflow-y-auto custom-scrollbar">
                
                <!-- Description -->
                <div class="mb-8">
                    <h2 class="h4 mb-3 border-b border-white/10 pb-2">توضیحات</h2>
                    @if ($course->excerpt)
                        <div class="text-lg mb-4 text-white/90">{{ $course->excerpt }}</div>
                    @endif

                    @if (($course->description ?? '') !== '')
                        <div class="text-white/80 leading-relaxed space-y-4">
                            @foreach (preg_split("/\\n\\s*\\n/", (string) $course->description) as $paragraph)
                                @php($paragraphText = trim((string) $paragraph))
                                @if ($paragraphText !== '')
                                    <p>{{ $paragraphText }}</p>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">توضیحاتی برای این دوره ثبت نشده است.</p>
                    @endif
                </div>

                <!-- Syllabus -->
                <div>
                    <h2 class="h4 mb-4 border-b border-white/10 pb-2">سرفصل‌های دوره</h2>
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
                                                    <div class="flex items-center gap-3">
                                                        <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-xs text-muted">{{ $loop->iteration }}</span>
                                                        <span>{{ $lesson->title }}</span>
                                                    </div>
                                                    @if (($isPurchased ?? false))
                                                         <span class="text-green-400 text-xs">آزاد</span>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
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
    </div>
@endsection
