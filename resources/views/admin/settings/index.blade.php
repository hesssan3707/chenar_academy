@extends('layouts.app')

@section('title', 'تنظیمات')

@section('content')
    @include('admin.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">تنظیمات</h1>
            <p class="page-subtitle">تنظیمات پایه سایت و قالب بصری</p>

            <div class="panel max-w-md">
                <form method="post" action="{{ route('admin.settings.update') }}" class="stack stack--sm">
                    @csrf
                    @method('put')

                    <label class="field">
                        <span class="field__label">قالب (Theme)</span>
                        <select name="theme" required>
                            @foreach ($themes as $theme)
                                <option value="{{ $theme }}" @selected($activeTheme === $theme)>{{ $theme }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
