@extends('layouts.admin')

@section('title', $title ?? 'نتایج نظرسنجی')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'نتایج نظرسنجی' }}</h1>
                    <p class="page-subtitle">{{ $survey->question }}</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.surveys.index') }}">بازگشت</a>
                    <a class="btn btn--primary" href="{{ route('admin.surveys.edit', $survey->id) }}">ویرایش</a>
                </div>
            </div>

            <div class="grid admin-grid-2">
                <div class="panel">
                    <h2 class="section-title">اطلاعات</h2>
                    <div class="stack stack--xs">
                        <div>شناسه: {{ $survey->id }}</div>
                        <div>
                            مخاطب:
                            @php($audience = (string) $survey->audience)
                            @if ($audience === 'authenticated')
                                فقط کاربران واردشده
                            @elseif ($audience === 'purchasers')
                                فقط خریداران
                            @else
                                همه کاربران
                            @endif
                        </div>
                        <div>وضعیت: {{ $survey->is_active ? 'فعال' : 'غیرفعال' }}</div>
                        <div>
                            بازه:
                            {{ $survey->starts_at ? jdate($survey->starts_at)->format('Y/m/d H:i') : 'از زمان ایجاد' }}
                            تا
                            {{ $survey->ends_at ? jdate($survey->ends_at)->format('Y/m/d H:i') : 'بدون پایان' }}
                        </div>
                        <div>تعداد پاسخ‌ها: {{ number_format((int) ($totalResponses ?? 0)) }}</div>
                    </div>
                </div>

                <div class="panel">
                    <h2 class="section-title">نتایج</h2>
                    @php($rows = collect($rows ?? []))
                    @php($otherRows = collect($otherRows ?? []))
                    @if ($rows->isEmpty() && $otherRows->isEmpty())
                        <p class="page-subtitle">هنوز پاسخی ثبت نشده است.</p>
                    @else
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>گزینه</th>
                                        <th>تعداد</th>
                                        <th>درصد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        <tr>
                                            <td>{{ (string) ($row['option'] ?? '') }}</td>
                                            <td class="admin-nowrap">{{ number_format((int) ($row['count'] ?? 0)) }}</td>
                                            <td class="admin-nowrap">{{ number_format((float) ($row['percentage'] ?? 0), 1) }}%</td>
                                        </tr>
                                    @endforeach
                                    @foreach ($otherRows as $row)
                                        <tr>
                                            <td>{{ (string) ($row['answer'] ?? '') }}</td>
                                            <td class="admin-nowrap">{{ number_format((int) ($row['count'] ?? 0)) }}</td>
                                            <td class="admin-nowrap">{{ number_format((float) ($row['percentage'] ?? 0), 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="panel">
                <h2 class="section-title">آخرین پاسخ‌ها</h2>
                @php($latestResponses = collect($latestResponses ?? []))
                @if ($latestResponses->isEmpty())
                    <p class="page-subtitle">پاسخی وجود ندارد.</p>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>کاربر</th>
                                    <th>پاسخ</th>
                                    <th>زمان</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($latestResponses as $response)
                                    @php($user = $response->user ?? null)
                                    @php($userLabel = $user?->name ?: ($user?->phone ?: ($response->user_id ?: 'مهمان')))
                                    <tr>
                                        <td class="admin-nowrap">{{ $userLabel }}</td>
                                        <td>{{ (string) ($response->answer ?? '') }}</td>
                                        <td class="admin-nowrap">{{ $response->answered_at ? jdate($response->answered_at)->format('Y/m/d H:i') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
