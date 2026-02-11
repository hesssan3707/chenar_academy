@extends('layouts.spa')

@section('title', 'درباره چنار آکادمی')

@section('content')
    <div class="spa-page">
        <div class="mb-6">
            <h1 class="h1 text-white mb-2">{{ $about['title'] ?? 'درباره چنار آکادمی' }}</h1>
            <div class="w-20 h-1 bg-brand rounded-full mb-4"></div>
            <p class="text-xl text-muted">{{ $about['subtitle'] ?? 'آموزش هدفمند برای مسیر حرفه‌ای' }}</p>
            <div class="flex flex-wrap gap-3 mt-6">
                <span class="badge badge--brand">یادگیری هدفمند</span>
                <span class="badge">منتورهای متخصص</span>
                <span class="badge">پشتیبانی مداوم</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <div class="lg:col-span-7 stack stack--md">
                <div class="panel p-6 bg-white/5 rounded-3xl border border-white/10 backdrop-blur-xl shadow-2xl">
                    <div class="stack stack--sm text-sm leading-relaxed text-gray-300">
                        @php($body = trim((string) ($about['body'] ?? '')))
                        @if(empty($body))
                            <p>چنار آکادمی با هدف ارائه آموزش‌های با کیفیت و کاربردی در حوزه‌های مختلف مهندسی و نرم‌افزار فعالیت می‌کند.</p>
                            <p>تیم ما متشکل از متخصصانی است که تجربه‌ی سال‌ها کار در صنعت و تدریس را دارند.</p>
                        @else
                            @foreach (preg_split("/\n\s*\n/", $body) as $paragraph)
                                @php($paragraphText = trim((string) $paragraph))
                                @if ($paragraphText !== '' && $loop->index < 2)
                                    <p>{{ $paragraphText }}</p>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="h-scroll-container">
                    <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 220px;">
                        <div class="text-brand text-xs font-bold mb-2">مسیر یادگیری</div>
                        <div class="text-white text-base font-bold mb-2">قدم‌به‌قدم</div>
                        <p class="text-muted text-xs leading-relaxed">پروژه‌محور و قابل اجرا</p>
                    </div>
                    <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 220px;">
                        <div class="text-brand text-xs font-bold mb-2">کیفیت محتوا</div>
                        <div class="text-white text-base font-bold mb-2">به‌روز و کاربردی</div>
                        <p class="text-muted text-xs leading-relaxed">متناسب با بازار کار</p>
                    </div>
                    <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 220px;">
                        <div class="text-brand text-xs font-bold mb-2">پشتیبانی</div>
                        <div class="text-white text-base font-bold mb-2">همیشه همراه</div>
                        <p class="text-muted text-xs leading-relaxed">پاسخ‌گویی سریع</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 stack stack--md">
                <div class="panel p-6 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-xl">
                    <h3 class="h4 mb-3 text-white">چرا چنار آکادمی؟</h3>
                    <div class="stack stack--xs text-xs text-muted leading-relaxed">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-brand"></span>
                            <span>تمرکز بر مهارت‌های کاربردی</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-brand"></span>
                            <span>یادگیری منعطف برای همه</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-brand"></span>
                            <span>ترکیب ویدیو و جزوه</span>
                        </div>
                    </div>
                </div>

                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-muted mb-1">شروع مسیر حرفه‌ای</div>
                            <div class="text-base font-bold text-white">با دوره‌ها و ویدیوها</div>
                        </div>
                        <a href="{{ route('products.index', ['type' => 'video']) }}" class="btn btn--primary btn--sm">مشاهده ویدیوها</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
