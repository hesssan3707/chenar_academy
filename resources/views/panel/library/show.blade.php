@extends('layouts.app')

@section('title', $title ?? ($product->title ?? 'محتوا'))

@section('content')
    @include('panel.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $product->title }}</h1>
            <p class="page-subtitle">
                @if ($product->type === 'course')
                    دوره آموزشی
                @elseif ($product->type === 'video')
                    ویدیو آموزشی
                @else
                    جزوه آموزشی
                @endif
            </p>

            @if ($product->type === 'course')
                @php($sections = $product->course?->sections ?? collect())
                @if ($sections->isEmpty())
                    <div class="panel max-w-md" style="margin-top: 18px;">
                        <p class="page-subtitle" style="margin: 0;">برای این دوره هنوز محتوایی ثبت نشده است.</p>
                    </div>
                @else
                    <div class="stack" style="margin-top: 18px;">
                        @foreach ($sections as $section)
                            <div class="panel">
                                <div class="stack stack--sm">
                                    <div class="section__title" style="font-size: 18px;">{{ $section->title }}</div>

                                    @php($lessons = $section->lessons ?? collect())
                                    @if ($lessons->isEmpty())
                                        <div class="card__meta">هنوز درسی برای این بخش ثبت نشده است.</div>
                                    @else
                                        <div class="stack stack--sm">
                                            @foreach ($lessons as $lesson)
                                                <div class="panel" style="background: rgba(15,26,46,0.35); border-style: dashed;">
                                                    <div class="stack stack--sm">
                                                        <div class="field__label">{{ $lesson->title }}</div>

                                                        @if ($lesson->lesson_type === 'video' && $lesson->media_id)
                                                            <video controls preload="metadata" style="width: 100%; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                                                                <source src="{{ route('panel.library.lessons.stream', ['product' => $product->slug, 'lesson' => $lesson->id]) }}" type="video/mp4">
                                                            </video>
                                                        @elseif ($lesson->lesson_type === 'text' && ($lesson->content ?? '') !== '')
                                                            <div class="stack stack--sm">
                                                                @foreach (preg_split("/\\n\\s*\\n/", (string) $lesson->content) as $paragraph)
                                                                    @php($paragraphText = trim((string) $paragraph))
                                                                    @if ($paragraphText !== '')
                                                                        <p style="margin: 0;">{{ $paragraphText }}</p>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="card__meta">محتوای این بخش در دسترس نیست.</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                @php($parts = $product->parts ?? collect())
                @php($singleVideoMediaId = $product->type === 'video' ? ($product->video?->media_id) : null)

                @if ($parts->isNotEmpty())
                    <div class="stack" style="margin-top: 18px;">
                        @foreach ($parts as $part)
                            <div class="panel">
                                <div class="stack stack--sm">
                                    <div class="section__title" style="font-size: 18px;">
                                        {{ $part->title ?: ('بخش '.($loop->index + 1)) }}
                                    </div>

                                    @if ($product->type === 'video' && $part->media_id)
                                        <video controls preload="metadata" style="width: 100%; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                                            <source src="{{ route('panel.library.parts.stream', ['product' => $product->slug, 'part' => $part->id]) }}" type="video/mp4">
                                        </video>
                                    @elseif ($product->type === 'note' && ($part->content ?? '') !== '')
                                        <div class="stack stack--sm">
                                            @foreach (preg_split("/\\n\\s*\\n/", (string) $part->content) as $paragraph)
                                                @php($paragraphText = trim((string) $paragraph))
                                                @if ($paragraphText !== '')
                                                    <p style="margin: 0;">{{ $paragraphText }}</p>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif ($product->type === 'note' && $part->media_id)
                                        <div class="form-actions">
                                            <a class="btn btn--primary"
                                                href="{{ route('panel.library.parts.stream', ['product' => $product->slug, 'part' => $part->id]) }}">دانلود</a>
                                        </div>
                                    @else
                                        <div class="card__meta">محتوای این بخش در دسترس نیست.</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif ($product->type === 'video' && $singleVideoMediaId)
                    <div class="panel" style="margin-top: 18px;">
                        <video controls preload="metadata" style="width: 100%; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                            <source src="{{ route('panel.library.video.stream', ['product' => $product->slug]) }}" type="video/mp4">
                        </video>
                        <div class="card__meta" style="margin-top: 10px;">این ویدیو فقط داخل سایت قابل مشاهده است.</div>
                    </div>
                @elseif ($product->type === 'note' && ($product->description ?? '') !== '')
                    <div class="panel max-w-md" style="margin-top: 18px;">
                        <div class="stack stack--sm">
                            @foreach (preg_split("/\\n\\s*\\n/", (string) $product->description) as $paragraph)
                                @php($paragraphText = trim((string) $paragraph))
                                @if ($paragraphText !== '')
                                    <p style="margin: 0;">{{ $paragraphText }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="panel max-w-md" style="margin-top: 18px;">
                        <p class="page-subtitle" style="margin: 0;">برای این محصول هنوز محتوایی ثبت نشده است.</p>
                    </div>
                @endif
            @endif

            @if (in_array($product->type, ['note', 'video'], true) && ! ($userReview ?? null))
                <div class="panel" style="margin-top: 18px;">
                    <div class="stack stack--sm">
                        <div class="field__label">ثبت نظر و امتیاز</div>
                        <form method="post" action="{{ route('products.reviews.store', $product->slug) }}" class="stack stack--sm">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                            <label class="field">
                                <span class="field__label">امتیاز (۱ تا ۵)</span>
                                <select name="rating" required>
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                                @error('rating')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">نظر (اختیاری)</span>
                                <textarea name="body" rows="4">{{ old('body', '') }}</textarea>
                                @error('body')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <div class="form-actions">
                                <button class="btn btn--primary" type="submit">ثبت</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="form-actions" style="margin-top: 18px;">
                <a class="btn btn--ghost" href="{{ route('panel.library.index') }}">بازگشت به کتابخانه</a>
            </div>
        </div>
    </section>
@endsection
