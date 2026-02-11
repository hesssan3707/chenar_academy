@extends('layouts.spa')

@section('title', 'ارتباط با ما')

@section('content')
    <div class="w-full h-full flex flex-col justify-center">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="h2 text-white mb-1">ارتباط با ما</h1>
            <p class="text-muted text-sm">مشتاق شنیدن نظرات و پیشنهادات شما هستیم</p>
        </div>

        <div class="w-full max-w-7xl mx-auto flex flex-col gap-6">
            <!-- Contact Info Row (Horizontal) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Phone -->
                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center text-brand shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] text-muted mb-0.5">تماس مستقیم</div>
                        <a href="tel:09377947853" class="text-sm font-bold text-white hover:text-brand transition-colors">09377947853</a>
                    </div>
                </div>

                <!-- Address -->
                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center text-brand shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    </div>
                    <div>
                        <div class="text-[10px] text-muted mb-0.5">آدرس دفتر</div>
                        <div class="text-sm text-white">تهران، خیابان انقلاب</div>
                    </div>
                </div>

                <!-- Socials -->
                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-between">
                    <div class="text-[10px] text-muted shrink-0">شبکه‌های اجتماعی</div>
                    <div class="flex gap-2">
                        <a href="https://t.me/chenar_academy" class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white hover:bg-[#229ED9] hover:border-[#229ED9] transition-all" title="Telegram">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </a>
                        <a href="https://instagram.com/chenar_academy" class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white hover:bg-[#E1306C] hover:border-[#E1306C] transition-all" title="Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                        </a>
                        <a href="https://youtube.com/@chenar_academy" class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white hover:bg-[#FF0000] hover:border-[#FF0000] transition-all" title="YouTube">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.42a2.78 2.78 0 0 0-1.94 2C1 8.14 1 12 1 12s0 3.86.46 5.58a2.78 2.78 0 0 0 1.94 2c1.72.42 8.6.42 8.6.42s6.88 0 8.6-.42a2.78 2.78 0 0 0 1.94-2C23 15.86 23 12 23 12s0-3.86-.46-5.58z"></path><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"></polygon></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form (Full Width) -->
            <div class="panel p-6 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-sm">
                <form method="post" action="{{ route('contact.submit') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @csrf
                    <div class="field">
                        <label class="field__label text-xs mb-1.5">نام و نام خانوادگی</label>
                        <input name="name" type="text" class="field__input bg-black/20 h-11 text-sm" placeholder="مثلا: علی علوی" required>
                    </div>
                    <div class="field">
                        <label class="field__label text-xs mb-1.5">شماره تماس یا ایمیل</label>
                        <input name="contact" type="text" class="field__input bg-black/20 h-11 text-sm" placeholder="0912... یا email@example.com" required>
                    </div>
                    <div class="md:col-span-2 field">
                        <label class="field__label text-xs mb-1.5">پیام شما</label>
                        <textarea name="message" rows="3" class="field__input bg-black/20 text-sm resize-none" placeholder="متن پیام خود را اینجا بنویسید..." required></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-center">
                        <button type="submit" class="btn btn--primary h-11 px-12 shadow-lg shadow-brand/20">ارسال پیام</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
