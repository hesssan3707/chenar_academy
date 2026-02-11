<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (PostTooLargeException $e, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return redirect()->back()->with('toast', [
                'type' => 'danger',
                'title' => 'حجم فایل زیاد است',
                'message' => 'حجم فایل ارسالی از محدودیت سرور بیشتر است. تنظیمات upload_max_filesize و post_max_size را بررسی کنید.',
            ]);
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            if ($request->is('admin*')) {
                return redirect()->guest(route('admin.login'))->with('toast', [
                    'type' => 'danger',
                    'title' => 'نیاز به ورود',
                    'message' => 'برای ورود به پنل مدیریت باید وارد شوید.',
                ]);
            }

            return redirect()->guest(route('login'))->with('toast', [
                'type' => 'danger',
                'title' => 'نیاز به ورود',
                'message' => 'برای انجام این کار باید وارد شوید.',
            ]);
        });
    }
}
