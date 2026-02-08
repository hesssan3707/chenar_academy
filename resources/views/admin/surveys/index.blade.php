@extends('layouts.app')

@section('title', $title ?? 'نظرسنجی‌ها')

@section('content')
    @include('admin.partials.nav')

    <section class="section">
        <div class="container">
            <div class="cluster" style="justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="page-title">{{ $title ?? 'نظرسنجی‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت نظرسنجی‌های کاربران</p>
                </div>
                <a class="btn btn--primary" href="{{ route('admin.surveys.create') }}">ایجاد نظرسنجی</a>
            </div>

            @php($surveys = $surveys ?? null)

            @if (! $surveys || $surveys->isEmpty())
                <div class="panel max-w-md" style="margin-top: 18px;">
                    <p class="page-subtitle" style="margin: 0;">هنوز نظرسنجی‌ای ایجاد نشده است.</p>
                </div>
            @else
                <div class="stack" style="margin-top: 18px;">
                    @foreach ($surveys as $survey)
                        <div class="panel">
                            <div class="stack stack--sm">
                                <div class="field__label">{{ $survey->question }}</div>
                                <div class="card__meta">
                                    @php($audience = (string) $survey->audience)
                                    <span>مخاطب: </span>
                                    <span>
                                        @if ($audience === 'authenticated')
                                            فقط کاربران واردشده
                                        @elseif ($audience === 'purchasers')
                                            فقط خریداران
                                        @else
                                            همه کاربران
                                        @endif
                                    </span>
                                    <span style="margin: 0 8px;">•</span>
                                    <span>وضعیت: {{ $survey->is_active ? 'فعال' : 'غیرفعال' }}</span>
                                    <span style="margin: 0 8px;">•</span>
                                    <span>
                                        بازه:
                                        {{ $survey->starts_at?->format('Y-m-d H:i') ?? 'از زمان ایجاد' }}
                                        تا
                                        {{ $survey->ends_at?->format('Y-m-d H:i') ?? 'بدون پایان' }}
                                    </span>
                                </div>

                                <div class="form-actions">
                                    <a class="btn btn--ghost" href="{{ route('admin.surveys.edit', $survey->id) }}">ویرایش</a>
                                    <form method="post" action="{{ route('admin.surveys.destroy', $survey->id) }}">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn--ghost" type="submit">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div>
                        {{ $surveys->links() }}
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
