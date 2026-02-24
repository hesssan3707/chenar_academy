@extends('layouts.admin')

@section('title', $title ?? 'دوره')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'دوره' }}</h1>
                    <p class="page-subtitle">اطلاعات دوره را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.courses.index') }}">بازگشت</a>
                </div>
            </div>

            @php($courseProduct = $courseProduct ?? null)
            @php($course = $course ?? null)
            @php($isEdit = $courseProduct && $courseProduct->exists)
            @php($institutions = $institutions ?? collect())
            @php($categories = $categories ?? collect())

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.courses.update', $courseProduct->id) : route('admin.courses.store') }}"
                    enctype="multipart/form-data"
                    class="stack stack--sm"
                    id="course-form"
                    data-discount-unit-form
                    data-currency-unit="{{ $commerceCurrencyLabel ?? 'ریال' }}">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">عنوان</span>
                        <input name="title" required value="{{ old('title', (string) ($courseProduct->title ?? '')) }}">
                        @error('title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع دانشگاه</span>
                            @php($institutionValue = old('institution_category_id', (string) ($courseProduct->institution_category_id ?? '')))
                            <select name="institution_category_id" required>
                                <option value="" @selected($institutionValue === '')>—</option>
                                @foreach ($institutions as $institution)
                                    <option value="{{ $institution->id }}" @selected((string) $institution->id === (string) $institutionValue)>
                                        {{ $institution->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('institution_category_id')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">دسته‌بندی</span>
                            @php($categoryValue = old('category_id', (string) ($isEdit ? ($courseProduct?->categories()->where('type', 'course')->value('categories.id') ?? '') : '')))
                            <select name="category_id" required>
                                <option value="" @selected($categoryValue === '')>—</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $category->id === (string) $categoryValue)>
                                        {{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">زمان انتشار</span>
                            <input name="published_at" data-jdp value="{{ old('published_at', $courseProduct?->published_at ? jdate($courseProduct->published_at)->format('Y/m/d H:i') : '') }}">
                            @error('published_at')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">توضیحات</span>
                        <textarea name="description">{{ old('description', (string) ($courseProduct->description ?? '')) }}</textarea>
                        @error('description')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">قیمت</span>
                            <div class="input-group">
                                <input type="number" name="base_price" min="0" max="2000000000"
                                    value="{{ old('base_price', (string) ($courseProduct->base_price ?? '')) }}">
                                <span class="card__meta">{{ $commerceCurrencyLabel ?? 'ریال' }}</span>
                            </div>
                            @error('base_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">کاور (تصویر)</span>
                            <input type="file" name="cover_image" accept="image/*">
                            @error('cover_image')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع تخفیف</span>
                            @php($discountTypeValue = old('discount_type', (string) ($courseProduct->discount_type ?? '')))
                            <select name="discount_type">
                                <option value="" @selected($discountTypeValue === '')>—</option>
                                <option value="percent" @selected($discountTypeValue === 'percent')>percent</option>
                                <option value="amount" @selected($discountTypeValue === 'amount')>amount</option>
                            </select>
                            @error('discount_type')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">مقدار تخفیف</span>
                            <div class="input-group">
                                <input type="number" name="discount_value" min="0" max="2000000000"
                                    value="{{ old('discount_value', (string) ($courseProduct->discount_value ?? '')) }}">
                                <span class="card__meta" data-discount-unit>{{ $discountTypeValue === 'percent' ? '٪' : ($commerceCurrencyLabel ?? 'ریال') }}</span>
                            </div>
                            @error('discount_value')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="divider"></div>

                    <div class="stack stack--sm">
                        <div class="section__title section__title--sm">ویدیوهای دوره</div>

                        @php($sections = $courseProduct?->course?->sections ?? collect())
                        @php($existingLessons = $sections->flatMap(fn ($section) => $section->lessons ?? collect()))

                        @if ($existingLessons->isNotEmpty())
                            <div class="table-wrap">
                                <table class="table" style="min-width: 1180px;">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>عنوان</th>
                                            <th>رایگان</th>
                                            <th>لینک</th>
                                            <th>فایل جدید</th>
                                            <th>حذف</th>
                                        </tr>
                                    </thead>
                                    <tbody data-course-lessons-list>
                                        @foreach ($existingLessons as $lesson)
                                            <tr draggable="false">
                                                <td class="admin-nowrap">
                                                    <button class="btn btn--ghost btn--sm" type="button" data-drag-handle>≡</button>
                                                </td>
                                                <td class="admin-min-w-260">
                                                    <input type="hidden" name="lessons[{{ $lesson->id }}][id]" value="{{ $lesson->id }}">
                                                    <input type="hidden" name="lessons[{{ $lesson->id }}][sort_order]" data-sort-order value="{{ old('lessons.'.$lesson->id.'.sort_order', (string) ($lesson->sort_order ?? 0)) }}">
                                                    <input name="lessons[{{ $lesson->id }}][title]" required value="{{ old('lessons.'.$lesson->id.'.title', (string) ($lesson->title ?? '')) }}">
                                                </td>
                                                <td class="admin-nowrap">
                                                    @php($isPreviewValue = (string) old('lessons.'.$lesson->id.'.is_preview', $lesson->is_preview ? '1' : '0'))
                                                    <label class="field" style="margin: 0;">
                                                        <input type="hidden" name="lessons[{{ $lesson->id }}][is_preview]" value="0">
                                                        <input type="checkbox" name="lessons[{{ $lesson->id }}][is_preview]" value="1" @checked($isPreviewValue === '1')>
                                                    </label>
                                                </td>
                                                <td class="admin-min-w-260">
                                                    <input type="url" dir="ltr" name="lessons[{{ $lesson->id }}][video_url]" value="{{ old('lessons.'.$lesson->id.'.video_url', (string) ($lesson->video_url ?? '')) }}">
                                                </td>
                                                <td class="admin-nowrap">
                                                    <input type="file" name="lessons[{{ $lesson->id }}][file]" accept="video/*">
                                                </td>
                                                <td class="admin-nowrap">
                                                    @php($deleteValue = (string) old('lessons.'.$lesson->id.'.delete', '0'))
                                                    <label class="field" style="margin: 0;">
                                                        <input type="hidden" name="lessons[{{ $lesson->id }}][delete]" value="0">
                                                        <input type="checkbox" name="lessons[{{ $lesson->id }}][delete]" value="1" @checked($deleteValue === '1')>
                                                    </label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="panel panel--soft">
                            <div class="stack stack--sm">
                                <div class="field__label">افزودن ویدیو جدید</div>
                                <div class="table-wrap">
                                    <table class="table" style="min-width: 1180px;">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>عنوان</th>
                                                <th>رایگان</th>
                                                <th>لینک</th>
                                                <th>فایل</th>
                                                <th>حذف</th>
                                            </tr>
                                        </thead>
                                        @php($oldLessons = old('lessons', []))
                                        @php($oldLessonKeys = is_array($oldLessons) ? array_keys($oldLessons) : [])
                                        @php($oldNewKeys = collect($oldLessonKeys)->filter(fn ($key) => str_starts_with((string) $key, 'new_'))->values())
                                        @php($newRowsCount = max(1, (int) $oldNewKeys->count()))
                                        <tbody data-course-new-lessons data-course-lessons-list data-next-index="{{ $newRowsCount }}">
                                            @for ($i = 0; $i < $newRowsCount; $i++)
                                                @php($key = (string) ($oldNewKeys[$i] ?? ('new_'.$i)))
                                                <tr draggable="false">
                                                    <td class="admin-nowrap">
                                                        <button class="btn btn--ghost btn--sm" type="button" data-drag-handle>≡</button>
                                                    </td>
                                                    <td class="admin-min-w-260">
                                                        <input type="hidden" name="lessons[{{ $key }}][sort_order]" data-sort-order value="{{ old('lessons.'.$key.'.sort_order', (string) $i) }}">
                                                        <input name="lessons[{{ $key }}][title]" value="{{ old('lessons.'.$key.'.title', '') }}">
                                                    </td>
                                                    <td class="admin-nowrap">
                                                        @php($isPreviewValue = (string) old('lessons.'.$key.'.is_preview', '0'))
                                                        <label class="field" style="margin: 0;">
                                                            <input type="hidden" name="lessons[{{ $key }}][is_preview]" value="0">
                                                            <input type="checkbox" name="lessons[{{ $key }}][is_preview]" value="1" @checked($isPreviewValue === '1')>
                                                        </label>
                                                    </td>
                                                    <td class="admin-min-w-260">
                                                        <input type="url" dir="ltr" name="lessons[{{ $key }}][video_url]" value="{{ old('lessons.'.$key.'.video_url', '') }}">
                                                    </td>
                                                    <td class="admin-nowrap">
                                                        <input type="file" name="lessons[{{ $key }}][file]" accept="video/*">
                                                    </td>
                                                    <td class="admin-nowrap">
                                                        <button class="btn btn--ghost btn--sm" type="button" data-course-remove-lesson>حذف</button>
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>

                                <div class="form-actions" style="justify-content: flex-start;">
                                    <button class="btn btn--sm btn--ghost" type="button" data-course-add-lesson>+</button>
                                </div>

                                <template data-course-lesson-row-template>
                                    <tr draggable="false">
                                        <td class="admin-nowrap">
                                            <button class="btn btn--ghost btn--sm" type="button" data-drag-handle>≡</button>
                                        </td>
                                        <td class="admin-min-w-260">
                                            <input type="hidden" name="lessons[__KEY__][sort_order]" data-sort-order value="__INDEX__">
                                            <input name="lessons[__KEY__][title]" value="">
                                        </td>
                                        <td class="admin-nowrap">
                                            <label class="field" style="margin: 0;">
                                                <input type="hidden" name="lessons[__KEY__][is_preview]" value="0">
                                                <input type="checkbox" name="lessons[__KEY__][is_preview]" value="1">
                                            </label>
                                        </td>
                                        <td class="admin-min-w-260">
                                            <input type="url" dir="ltr" name="lessons[__KEY__][video_url]" value="">
                                        </td>
                                        <td class="admin-nowrap">
                                            <input type="file" name="lessons[__KEY__][file]" accept="video/*">
                                        </td>
                                        <td class="admin-nowrap">
                                            <button class="btn btn--ghost btn--sm" type="button" data-course-remove-lesson>حذف</button>
                                        </td>
                                    </tr>
                                </template>

                                @error('lessons')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" name="intent" value="save" form="course-form">ذخیره</button>
                    <button class="btn btn--ghost" type="submit" name="intent" value="publish" form="course-form">انتشار</button>
                    @if ($isEdit && (string) $courseProduct->status === 'published')
                        <button class="btn btn--ghost" type="submit" name="intent" value="draft" form="course-form">تبدیل به پیش‌نویس</button>
                    @endif
                    @if ($isEdit)
                        <button class="btn btn--danger" type="submit" form="course-delete-form">حذف دوره</button>
                    @endif
                </div>

                @if ($isEdit)
                    <form method="post"
                        action="{{ route('admin.courses.destroy', $courseProduct->id) }}"
                        id="course-delete-form"
                        data-confirm="1"
                        data-confirm-title="حذف دوره"
                        data-confirm-message="آیا از حذف این دوره مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
