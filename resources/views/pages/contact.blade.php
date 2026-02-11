@extends('layouts.spa')

@section('title', 'ارتباط با ما')

@section('content')
    <div class="spa-page">
        <div class="mb-6">
            <h1 class="h1 text-white mb-2">ارتباط با ما</h1>
            <div class="w-20 h-1 bg-brand rounded-full mb-4"></div>
            <p class="text-xl text-muted">پشتیبانی، همکاری و پیشنهادات شما را با افتخار می‌شنویم</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <div class="lg:col-span-5">
                <div class="h-scroll-container">
                    <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 220px;">
                        <div class="flex items-center gap-3 mb-3 text-brand">
                            <div class="w-9 h-9 rounded-xl bg-brand/10 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-white">تماس مستقیم</h3>
                        </div>
                        <a href="tel:09377947853" class="text-lg font-bold text-white hover:text-brand transition-colors block mb-1">09377947853</a>
                        <p class="text-muted text-xs leading-relaxed">شنبه تا پنجشنبه، ۹ تا ۱۸</p>
                    </div>

                    <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 220px;">
                        <div class="flex items-center gap-3 mb-3 text-brand">
                            <div class="w-9 h-9 rounded-xl bg-brand/10 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 1118 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </div>
                            <h3 class="text-base font-bold text-white">آدرس دفتر</h3>
                        </div>
                        <p class="text-muted text-xs leading-relaxed">تهران، خیابان انقلاب، نزدیک به دانشگاه</p>
                    </div>

                    <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 220px;">
                        <div class="flex items-center gap-3 mb-3 text-brand">
                            <div class="w-9 h-9 rounded-xl bg-brand/10 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="text-base font-bold text-white">شبکه‌های اجتماعی</h3>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            @php($links = ($socialLinks ?? collect())->where('url', '!=', ''))
                            @if ($links->isEmpty())
                                <a href="https://t.me/chenar_academy" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand hover:border-brand transition-all">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M21.9 5.24c.2-.88-.75-1.55-1.55-1.22L2.9 11.1c-.93.37-.86 1.73.1 1.98l4.78 1.23 1.83 5.5c.28.83 1.37.93 1.8.16l2.7-4.74 4.82 3.55c.72.53 1.74.12 1.92-.77l2.05-12.77ZM9.3 13.63l9.25-5.84-7.46 7.13-.28 2.84-1.46-4.38-3.21-.83Z" /></svg>
                                </a>
                                <a href="https://instagram.com/chenar_academy" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand hover:border-brand transition-all">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="3" /><path d="M12 13.4a1.4 1.4 0 1 0 0-2.8a1.4 1.4 0 0 0 0 2.8Z" /><path d="M16.2 7.8h.01" /></svg>
                                </a>
                            @else
                                @foreach($links as $link)
                                    <a href="{{ $link->url }}" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand hover:border-brand transition-all" title="{{ $link->title }}">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M10 14a5 5 0 0 1 0-7l.7-.7a5 5 0 0 1 7.1 7.1l-.7.7" /></svg>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="panel p-6 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-xl shadow-2xl h-full">
                    <div class="mb-4">
                        <h2 class="h3 text-white mb-1">فرم تماس</h2>
                        <p class="text-muted text-xs">برای همکاری یا پشتیبانی پیام خود را ارسال کنید</p>
                    </div>
                    <form method="post" action="{{ route('contact.submit') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        <div class="field">
                            <label class="field__label">نام شما</label>
                            <input name="name" type="text" class="field__input bg-black/20" placeholder="مثلا: علی علوی" required>
                        </div>
                        <div class="field">
                            <label class="field__label">ایمیل یا شماره تماس</label>
                            <input name="contact" type="text" class="field__input bg-black/20" placeholder="info@example.com" required>
                        </div>
                        <div class="md:col-span-2 field">
                            <label class="field__label">پیام شما</label>
                            <textarea name="message" rows="4" class="field__input bg-black/20" placeholder="متن پیام خود را اینجا بنویسید..." required></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn btn--primary w-full">ارسال پیام</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
