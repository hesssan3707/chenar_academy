@extends('layouts.spa')

@section('title', $title ?? ($product->title ?? 'محتوا'))

@section('content')
    <div class="container h-full py-6">
        <div class="user-panel-grid">
            @include('panel.partials.sidebar')

            <main class="user-content flex flex-col overflow-hidden">
                <div class="mb-6">
                    <h2 class="h2 mb-1">{{ $product->title }}</h2>
                    <p class="text-muted">
                @if ($product->type === 'course')
                    دوره آموزشی
                @elseif ($product->type === 'video')
                    ویدیو آموزشی
                @else
                    جزوه آموزشی
                @endif
                    </p>
                </div>

                <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
            @php($placeholderThumb = asset('images/default_image.webp'))
            @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
            <div class="panel" style="margin-top: 18px;">
                <div class="spa-cover">
                    <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                </div>
            </div>

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
                                    <div class="section__title section__title--sm">{{ $section->title }}</div>

                                    @php($lessons = $section->lessons ?? collect())
                                    @if ($lessons->isEmpty())
                                        <div class="card__meta">هنوز درسی برای این بخش ثبت نشده است.</div>
                                    @else
                                        <div class="stack stack--sm">
                                            @foreach ($lessons as $lesson)
                                                <div class="panel panel--soft">
                                                    <div class="stack stack--sm">
                                                        <div class="field__label">{{ $lesson->title }}</div>

                                                        @if ($lesson->lesson_type === 'video' && $lesson->media_id)
                                                            <video class="product-detail__video" controls preload="metadata">
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
                                    <div class="section__title section__title--sm">
                                        {{ $part->title ?: ('بخش '.($loop->index + 1)) }}
                                    </div>

                                    @if ($product->type === 'video' && $part->media_id)
                                        <video class="product-detail__video" controls preload="metadata">
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
                        <video class="product-detail__video" controls preload="metadata">
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

            @if (in_array($product->type, ['note', 'video'], true) && ($userReview ?? null) && (string) ($userReview->status ?? '') === 'pending')
                <div class="panel panel--warning" style="margin-top: 18px;">
                    <div class="stack stack--sm">
                        <div class="field__label">در انتظار بررسی</div>
                        @if ($userReview->body)
                            <div>{{ $userReview->body }}</div>
                        @endif
                    </div>
                </div>
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
            </main>
        </div>
    </div>
