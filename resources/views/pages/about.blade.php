@extends('layouts.spa')

@section('title', 'درباره چنار آکادمی')

@section('content')
    <div class="container spa-page-shell" style="padding: 24px 0;">
        <header style="text-align: center; margin-bottom: 18px;">
            <h1 class="page-title">{{ $about['title'] ?? 'درباره چنار آکادمی' }}</h1>
            <p class="page-subtitle">{{ $about['subtitle'] ?? 'آموزش هدفمند برای مسیر حرفه‌ای' }}</p>
        </header>

        <div class="spa-page-scroll">
            <div class="stack" style="gap: 18px;">
                <div class="panel" style="padding: 22px;">
                    <div class="grid spa-grid--2" style="gap: 22px;">
                        @php($body = trim((string) ($about['body'] ?? '')))
                        @if(empty($body))
                            <div>
                                <p>چنار آکادمی با هدف ارائه آموزش‌های با کیفیت و کاربردی در حوزه‌های مختلف مهندسی و نرم‌افزار فعالیت می‌کند.</p>
                                <p>تیم ما متشکل از متخصصانی است که تجربه‌ی سال‌ها کار در صنعت و تدریس را دارند.</p>
                            </div>
                            <div>
                                <p>ما معتقدیم یادگیری باید مسیری روشن، عملی و نتیجه‌گرا باشد. در چنار آکادمی، تمرکز ما بر روی انتقال تجربیات واقعی و مهارت‌هایی است که در بازار کار مورد نیاز هستند.</p>
                                <div class="cluster" style="margin-top: 14px; gap: 8px;">
                                    <span class="badge badge--brand" style="font-size: 11px;">یادگیری هدفمند</span>
                                    <span class="badge" style="font-size: 11px;">منتورهای متخصص</span>
                                    <span class="badge" style="font-size: 11px;">پشتیبانی مداوم</span>
                                </div>
                            </div>
                        @else
                            @php($paragraphs = preg_split("/\n\s*\n/", $body))
                            <div>
                                @foreach ($paragraphs as $index => $paragraph)
                                    @if($index % 2 == 0 && $index < 4)
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                            </div>
                            <div>
                                @foreach ($paragraphs as $index => $paragraph)
                                    @if($index % 2 != 0 && $index < 4)
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                                <div class="cluster" style="margin-top: 14px; gap: 8px;">
                                    <span class="badge badge--brand" style="font-size: 11px;">یادگیری هدفمند</span>
                                    <span class="badge" style="font-size: 11px;">منتورهای متخصص</span>
                                    <span class="badge" style="font-size: 11px;">پشتیبانی مداوم</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid spa-grid--4">
                    <div class="panel" style="display: flex; align-items: center; gap: 14px;">
                        <div class="spa-icon-tile spa-icon-tile--round">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div>
                            <div style="font-weight: 900; font-size: 14px; margin-bottom: 2px;">مسیر شفاف</div>
                            <div style="font-size: 12px; color: var(--ds-muted);">قدم‌به‌قدم تا تخصص</div>
                        </div>
                    </div>

                    <div class="panel" style="display: flex; align-items: center; gap: 14px;">
                        <div class="spa-icon-tile spa-icon-tile--round">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <div>
                            <div style="font-weight: 900; font-size: 14px; margin-bottom: 2px;">کیفیت بالا</div>
                            <div style="font-size: 12px; color: var(--ds-muted);">محتوای به‌روز و ناب</div>
                        </div>
                    </div>

                    <div class="panel" style="display: flex; align-items: center; gap: 14px;">
                        <div class="spa-icon-tile spa-icon-tile--round">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div>
                            <div style="font-weight: 900; font-size: 14px; margin-bottom: 2px;">پشتیبانی</div>
                            <div style="font-size: 12px; color: var(--ds-muted);">همراه همیشگی شما</div>
                        </div>
                    </div>

                    <div class="panel" style="display: flex; align-items: center; gap: 14px;">
                        <div class="spa-icon-tile spa-icon-tile--round">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        </div>
                        <div>
                            <div style="font-weight: 900; font-size: 14px; margin-bottom: 2px;">پروژه‌محور</div>
                            <div style="font-size: 12px; color: var(--ds-muted);">آماده برای بازار کار</div>
                        </div>
                    </div>
                </div>

                <div class="panel" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; border-color: rgba(45, 212, 191, 0.22); background: rgba(45, 212, 191, 0.08);">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div class="spa-icon-tile spa-icon-tile--round" style="background: var(--ds-brand); border-color: transparent; color: white;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: rgba(232, 238, 252, 0.86); font-weight: 900; margin-bottom: 2px;">شروع یادگیری</div>
                            <div style="font-size: 14px; font-weight: 900;">به جمع متخصصین چنار آکادمی بپیوندید</div>
                        </div>
                    </div>
                    <a href="{{ route('products.index', ['type' => 'video']) }}" class="btn btn--primary">مشاهده دوره‌ها</a>
                </div>
            </div>
        </div>
    </div>
@endsection
