@extends('layouts.spa')

@section('title', 'درباره چنار آکادمی')

@section('content')
    <div class="w-full h-full flex flex-col justify-center">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="h2 text-white mb-1">{{ $about['title'] ?? 'درباره چنار آکادمی' }}</h1>
            <p class="text-muted text-sm">{{ $about['subtitle'] ?? 'آموزش هدفمند برای مسیر حرفه‌ای' }}</p>
        </div>

        <div class="w-full max-w-7xl mx-auto flex flex-col gap-6">
            <!-- Content Panel (Full Width) -->
            <div class="panel p-6 bg-white/5 rounded-3xl border border-white/10 backdrop-blur-xl">
                <div class="prose prose-invert prose-sm max-w-none text-gray-300 leading-relaxed grid grid-cols-1 md:grid-cols-2 gap-8">
                    @php($body = trim((string) ($about['body'] ?? '')))
                    @if(empty($body))
                        <div>
                            <p>چنار آکادمی با هدف ارائه آموزش‌های با کیفیت و کاربردی در حوزه‌های مختلف مهندسی و نرم‌افزار فعالیت می‌کند.</p>
                            <p>تیم ما متشکل از متخصصانی است که تجربه‌ی سال‌ها کار در صنعت و تدریس را دارند.</p>
                        </div>
                        <div>
                            <p>ما معتقدیم یادگیری باید مسیری روشن، عملی و نتیجه‌گرا باشد. در چنار آکادمی، تمرکز ما بر روی انتقال تجربیات واقعی و مهارت‌هایی است که در بازار کار مورد نیاز هستند.</p>
                            <div class="flex flex-wrap gap-2 mt-4">
                                <span class="px-3 py-1 rounded-full bg-brand/10 text-brand text-[10px] font-bold">یادگیری هدفمند</span>
                                <span class="px-3 py-1 rounded-full bg-white/5 text-white text-[10px]">منتورهای متخصص</span>
                                <span class="px-3 py-1 rounded-full bg-white/5 text-white text-[10px]">پشتیبانی مداوم</span>
                            </div>
                        </div>
                    @else
                        @php($paragraphs = preg_split("/\n\s*\n/", $body))
                        <div class="space-y-4">
                            @foreach ($paragraphs as $index => $paragraph)
                                @if($index % 2 == 0 && $index < 4)
                                    <p>{{ trim($paragraph) }}</p>
                                @endif
                            @endforeach
                        </div>
                        <div class="space-y-4">
                            @foreach ($paragraphs as $index => $paragraph)
                                @if($index % 2 != 0 && $index < 4)
                                    <p>{{ trim($paragraph) }}</p>
                                @endif
                            @endforeach
                            <div class="flex flex-wrap gap-2 mt-4">
                                <span class="px-3 py-1 rounded-full bg-brand/10 text-brand text-[10px] font-bold">یادگیری هدفمند</span>
                                <span class="px-3 py-1 rounded-full bg-white/5 text-white text-[10px]">منتورهای متخصص</span>
                                <span class="px-3 py-1 rounded-full bg-white/5 text-white text-[10px]">پشتیبانی مداوم</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Features Row (Horizontal) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-brand/10 flex items-center justify-center text-brand shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white mb-0.5">مسیر شفاف</h3>
                        <p class="text-[10px] text-muted">قدم‌به‌قدم تا تخصص</p>
                    </div>
                </div>

                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-brand/10 flex items-center justify-center text-brand shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white mb-0.5">کیفیت بالا</h3>
                        <p class="text-[10px] text-muted">محتوای به‌روز و ناب</p>
                    </div>
                </div>

                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-brand/10 flex items-center justify-center text-brand shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white mb-0.5">پشتیبانی</h3>
                        <p class="text-[10px] text-muted">همراه همیشگی شما</p>
                    </div>
                </div>

                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-brand/10 flex items-center justify-center text-brand shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white mb-0.5">پروژه‌محور</h3>
                        <p class="text-[10px] text-muted">آماده برای بازار کار</p>
                    </div>
                </div>
            </div>

            <!-- CTA Bar (Horizontal) -->
            <div class="panel p-5 bg-gradient-to-r from-brand/20 to-transparent border border-brand/20 rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-brand flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs text-brand font-bold mb-0.5">شروع یادگیری</div>
                        <div class="text-sm font-bold text-white">به جمع متخصصین چنار آکادمی بپیوندید</div>
                    </div>
                </div>
                <a href="{{ route('products.index', ['type' => 'video']) }}" class="btn btn--primary h-10 px-8 shadow-lg shadow-brand/20">مشاهده دوره‌ها</a>
            </div>
        </div>
    </div>
@endsection
