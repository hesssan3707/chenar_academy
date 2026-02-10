@extends('layouts.spa')

@section('title', 'درباره چنار آکادمی')

@section('content')
    <div class="spa-page">
        <div class="mb-10">
            <h1 class="h1 text-white mb-2">{{ $about['title'] ?? 'درباره چنار آکادمی' }}</h1>
            <div class="w-20 h-1 bg-brand rounded-full mb-4"></div>
            <p class="text-xl text-muted">{{ $about['subtitle'] ?? 'آموزش هدفمند برای مسیر حرفه‌ای' }}</p>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar pl-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                <div class="lg:col-span-7 stack stack--lg">
                    <div class="panel p-8 bg-white/5 rounded-3xl border border-white/10 backdrop-blur-xl shadow-2xl">
                        <div class="stack stack--md text-lg leading-relaxed text-gray-300">
                            @php($body = trim((string) ($about['body'] ?? '')))
                            @if(empty($body))
                                <p>چنار آکادمی با هدف ارائه آموزش‌های با کیفیت و کاربردی در حوزه‌های مختلف مهندسی و نرم‌افزار فعالیت می‌کند. ما معتقدیم که یادگیری باید لذت‌بخش و در دسترس باشد.</p>
                                <p>تیم ما متشکل از متخصصانی است که تجربه‌ی سال‌ها کار در صنعت و تدریس را دارند و آماده‌اند تا دانش خود را با شما به اشتراک بگذارند.</p>
                            @else
                                @foreach (preg_split("/\n\s*\n/", $body) as $paragraph)
                                    @php($paragraphText = trim((string) $paragraph))
                                    @if ($paragraphText !== '')
                                        <p>{{ $paragraphText }}</p>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-6">
                        <div class="panel p-6 bg-brand/5 border border-brand/10 rounded-2xl text-center group hover:bg-brand/10 transition-all duration-300">
                            <div class="text-3xl font-bold text-brand mb-1 group-hover:scale-110 transition-transform">۵۰۰+</div>
                            <div class="text-xs text-muted uppercase tracking-wider">دانشجوی فعال</div>
                        </div>
                        <div class="panel p-6 bg-brand/5 border border-brand/10 rounded-2xl text-center group hover:bg-brand/10 transition-all duration-300">
                            <div class="text-3xl font-bold text-brand mb-1 group-hover:scale-110 transition-transform">۵۰+</div>
                            <div class="text-xs text-muted uppercase tracking-wider">دوره آموزشی</div>
                        </div>
                        <div class="panel p-6 bg-brand/5 border border-brand/10 rounded-2xl text-center group hover:bg-brand/10 transition-all duration-300">
                            <div class="text-3xl font-bold text-brand mb-1 group-hover:scale-110 transition-transform">۲۴/۷</div>
                            <div class="text-xs text-muted uppercase tracking-wider">پشتیبانی</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 hidden lg:block sticky top-0">
                    <div class="relative">
                        <div class="absolute -inset-20 bg-brand/10 blur-[120px] rounded-full animate-pulse"></div>
                        <div class="panel p-2 bg-white/5 border border-white/10 rounded-[2rem] overflow-hidden backdrop-blur-sm">
                            <img src="data:image/svg+xml;utf8,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4F46E5"/><stop offset="1" stop-color="#9333EA"/></linearGradient></defs><rect width="500" height="500" fill="url(#g)" fill-opacity="0.05"/><path d="M100 250 Q250 100 400 250 T400 400" fill="none" stroke="url(#g)" stroke-width="2" stroke-dasharray="20 10" opacity="0.3"/><circle cx="250" cy="250" r="150" fill="none" stroke="url(#g)" stroke-width="1" opacity="0.2"/><path d="M150 250 L350 250 M250 150 L250 350" stroke="url(#g)" stroke-width="1" opacity="0.2"/></svg>') }}" alt="About" class="w-full rounded-[1.8rem] opacity-60">
                        </div>
                        
                        <div class="absolute -bottom-6 -right-6 panel p-6 bg-white/10 border border-white/20 rounded-2xl backdrop-blur-xl shadow-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-ping"></div>
                                <span class="text-sm font-medium text-white">در حال رشد و توسعه</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
