@extends('layouts.spa')

@section('title', 'ارتباط با ما')

@section('content')
    <div class="spa-page">
        <div class="mb-10">
            <h1 class="h1 text-white mb-2">ارتباط با ما</h1>
            <div class="w-20 h-1 bg-brand rounded-full mb-4"></div>
            <p class="text-xl text-muted">منتظر شنیدن نظرات و پیشنهادات شما هستیم</p>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar pl-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                <!-- Contact Info Cards -->
                <div class="lg:col-span-4 stack stack--lg">
                    <div class="panel p-8 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-xl group hover:border-brand/30 transition-all duration-300 shadow-xl">
                        <div class="flex items-center gap-4 mb-6 text-brand">
                            <div class="w-12 h-12 rounded-2xl bg-brand/10 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white">تماس مستقیم</h3>
                        </div>
                        <a href="tel:09377947853" class="text-2xl font-bold text-white hover:text-brand transition-colors block mb-2">09377947853</a>
                        <p class="text-muted leading-relaxed">شنبه تا پنجشنبه، ۹ تا ۱۸<br>آماده پاسخگویی به سوالات شما هستیم.</p>
                    </div>

                    <div class="panel p-8 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-xl group hover:border-brand/30 transition-all duration-300 shadow-xl">
                        <div class="flex items-center gap-4 mb-6 text-brand">
                            <div class="w-12 h-12 rounded-2xl bg-brand/10 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white">شبکه‌های اجتماعی</h3>
                        </div>
                        <div class="flex gap-4">
                            @php($links = ($socialLinks ?? collect())->where('url', '!=', ''))
                            @if ($links->isEmpty())
                                <a href="https://t.me/chenar_academy" class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand hover:border-brand hover:-translate-y-1 transition-all duration-300">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M21.9 5.24c.2-.88-.75-1.55-1.55-1.22L2.9 11.1c-.93.37-.86 1.73.1 1.98l4.78 1.23 1.83 5.5c.28.83 1.37.93 1.8.16l2.7-4.74 4.82 3.55c.72.53 1.74.12 1.92-.77l2.05-12.77ZM9.3 13.63l9.25-5.84-7.46 7.13-.28 2.84-1.46-4.38-3.21-.83Z" /></svg>
                                </a>
                                <a href="https://instagram.com/chenar_academy" class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand hover:border-brand hover:-translate-y-1 transition-all duration-300">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="3" /><path d="M12 13.4a1.4 1.4 0 1 0 0-2.8a1.4 1.4 0 0 0 0 2.8Z" /><path d="M16.2 7.8h.01" /></svg>
                                </a>
                            @else
                                @foreach($links as $link)
                                    <a href="{{ $link->url }}" class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand hover:border-brand hover:-translate-y-1 transition-all duration-300" title="{{ $link->title }}">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M10 14a5 5 0 0 1 0-7l.7-.7a5 5 0 0 1 7.1 7.1l-.7.7" /></svg>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-8">
                    <div class="panel p-10 bg-white/5 border border-white/10 rounded-[2rem] backdrop-blur-xl shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-brand/5 blur-[100px] -mr-32 -mt-32 rounded-full"></div>
                        
                        <form method="post" action="{{ route('contact.submit') }}" class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8">
                            @csrf
                            <div class="field">
                                <label class="field__label text-gray-400 mb-2 block font-medium">نام شما</label>
                                <input name="name" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none placeholder-gray-600" placeholder="مثلا: علی علوی" required>
                            </div>
                            <div class="field">
                                <label class="field__label text-gray-400 mb-2 block font-medium">ایمیل یا شماره تماس</label>
                                <input name="contact" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none placeholder-gray-600" placeholder="info@example.com" required>
                            </div>
                            <div class="md:col-span-2 field">
                                <label class="field__label text-gray-400 mb-2 block font-medium">پیام شما</label>
                                <textarea name="message" rows="5" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none placeholder-gray-600 resize-none" placeholder="متن پیام خود را اینجا بنویسید..." required></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="btn btn--primary w-full py-5 rounded-2xl text-xl font-bold shadow-2xl shadow-brand/30 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                    ارسال پیام
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
