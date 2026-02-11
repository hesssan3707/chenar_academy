<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = ProductReview::query()->with(['product', 'user'])->orderByDesc('id');

        if (is_string($status) && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $reviews = $query->paginate(40)->withQueryString();

        return view('admin.reviews.index', [
            'title' => 'نظرات',
            'reviews' => $reviews,
            'activeStatus' => is_string($status) ? $status : null,
            'approvalRequired' => $this->settingBool('commerce.reviews.require_approval', false),
        ]);
    }

    public function edit(int $review): View
    {
        $reviewModel = ProductReview::query()->with(['product', 'user'])->findOrFail($review);

        return view('admin.reviews.form', [
            'title' => 'ویرایش نظر',
            'review' => $reviewModel,
        ]);
    }

    public function update(Request $request, int $review): RedirectResponse
    {
        $reviewModel = ProductReview::query()->findOrFail($review);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $reviewModel->forceFill([
            'rating' => (int) $validated['rating'],
            'body' => isset($validated['body']) && trim((string) $validated['body']) !== '' ? trim((string) $validated['body']) : null,
            'status' => (string) $validated['status'],
            'moderated_at' => now(),
        ])->save();

        return redirect()->route('admin.reviews.index');
    }

    public function destroy(int $review): RedirectResponse
    {
        $reviewModel = ProductReview::query()->findOrFail($review);
        $reviewModel->delete();

        return redirect()->route('admin.reviews.index');
    }

    public function approve(int $review): RedirectResponse
    {
        $reviewModel = ProductReview::query()->findOrFail($review);

        $reviewModel->forceFill([
            'status' => 'approved',
            'moderated_at' => now(),
        ])->save();

        return redirect()->route('admin.reviews.index');
    }

    public function reject(int $review): RedirectResponse
    {
        $reviewModel = ProductReview::query()->findOrFail($review);

        $reviewModel->forceFill([
            'status' => 'rejected',
            'moderated_at' => now(),
        ])->save();

        return redirect()->route('admin.reviews.index');
    }

    private function settingBool(string $key, bool $default): bool
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        $setting = Setting::query()->where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        $value = $setting->value;

        if (is_array($value)) {
            if (array_key_exists('enabled', $value) && is_bool($value['enabled'])) {
                return $value['enabled'];
            }

            if (array_key_exists('value', $value)) {
                $value = $value['value'];
            } elseif (count($value) === 1) {
                $value = reset($value);
            }
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) ((int) $value);
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return $default;
    }
}
