<?php

namespace App\Http\Controllers;

use App\Models\ProductAccess;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class SurveyResponseController extends Controller
{
    public function store(Request $request, Survey $survey): RedirectResponse
    {
        $now = now();
        abort_if(! $survey->is_active, 404);
        abort_if($survey->starts_at && $survey->starts_at->greaterThan($now), 404);
        abort_if($survey->ends_at && $survey->ends_at->lessThan($now), 404);

        $options = collect($survey->options)->filter(fn ($v) => is_string($v) && trim($v) !== '')->values()->all();

        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:500'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        abort_if(! in_array($validated['answer'], $options, true), 422);

        $user = $request->user();

        $audience = (string) $survey->audience;

        if ($audience === 'authenticated') {
            abort_if(! $user, 403);
        }

        if ($audience === 'purchasers') {
            abort_if(! $user, 403);

            $isPurchaser = ProductAccess::query()->where('user_id', $user->id)->exists();
            abort_if(! $isPurchaser, 403);
        }

        $cookieToken = (string) $request->cookie('survey_anon_token', '');
        if ($cookieToken !== '') {
            try {
                $decrypted = app('encrypter')->decrypt($cookieToken, false);
                if (is_string($decrypted) && $decrypted !== '') {
                    $cookieToken = \Illuminate\Cookie\CookieValuePrefix::remove($decrypted, 'survey_anon_token');
                }
            } catch (\Throwable) {
            }
        }
        $tokenToSet = null;

        if (! $user) {
            if ($cookieToken === '') {
                $cookieToken = Str::random(60);
                $tokenToSet = $cookieToken;
            }

            SurveyResponse::query()->updateOrCreate(
                ['survey_id' => $survey->id, 'anon_token' => $cookieToken],
                ['answer' => $validated['answer'], 'answered_at' => now(), 'meta' => []]
            );
        } else {
            SurveyResponse::query()->updateOrCreate(
                ['survey_id' => $survey->id, 'user_id' => $user->id],
                ['answer' => $validated['answer'], 'answered_at' => now(), 'meta' => []]
            );
        }

        $redirectTo = $validated['redirect_to'] ?? null;
        $response = redirect()->route('home');

        if (is_string($redirectTo) && $redirectTo !== '') {
            $baseUrl = url('/');
            if (str_starts_with($redirectTo, $baseUrl)) {
                $response = redirect($redirectTo);
            }
        }

        $response = $response->with('toast', [
            'type' => 'success',
            'title' => 'ثبت شد',
            'message' => 'پاسخ شما ثبت شد.',
        ]);

        if ($tokenToSet) {
            $response->withCookie(Cookie::forever('survey_anon_token', $tokenToSet));
        }

        return $response;
    }
}
