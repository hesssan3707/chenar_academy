<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(): View
    {
        $surveys = Survey::query()->orderByDesc('id')->paginate(40);

        return view('admin.surveys.index', [
            'title' => 'نظرسنجی‌ها',
            'surveys' => $surveys,
        ]);
    }

    public function create(): View
    {
        $survey = new Survey([
            'audience' => 'all',
            'is_active' => true,
            'starts_at' => now(),
        ]);

        return view('admin.surveys.form', [
            'title' => 'ایجاد نظرسنجی',
            'survey' => $survey,
        ]);
    }

    public function results(Survey $survey): View
    {
        $countsByAnswer = $survey->responses()
            ->select('answer', DB::raw('COUNT(*) as count'))
            ->groupBy('answer')
            ->orderByDesc('count')
            ->pluck('count', 'answer')
            ->all();

        $totalResponses = array_sum(array_map(fn ($value) => (int) $value, $countsByAnswer));
        $options = collect($survey->options ?? [])->map(fn ($value) => (string) $value)->values();

        $rows = $options->map(function (string $option) use ($countsByAnswer, $totalResponses) {
            $count = (int) ($countsByAnswer[$option] ?? 0);

            return [
                'option' => $option,
                'count' => $count,
                'percentage' => $totalResponses > 0 ? ($count / $totalResponses) * 100 : 0,
            ];
        });

        $otherRows = collect($countsByAnswer)
            ->map(fn ($count, $answer) => ['answer' => (string) $answer, 'count' => (int) $count])
            ->filter(fn ($row) => ! $options->contains($row['answer']))
            ->values()
            ->map(function (array $row) use ($totalResponses) {
                $row['percentage'] = $totalResponses > 0 ? ($row['count'] / $totalResponses) * 100 : 0;

                return $row;
            });

        $latestResponses = $survey->responses()
            ->with('user')
            ->orderByDesc('answered_at')
            ->orderByDesc('id')
            ->take(30)
            ->get();

        return view('admin.surveys.results', [
            'title' => 'نتایج نظرسنجی',
            'survey' => $survey,
            'rows' => $rows,
            'otherRows' => $otherRows,
            'totalResponses' => $totalResponses,
            'latestResponses' => $latestResponses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $survey = Survey::query()->create([
            'question' => $validated['question'],
            'options' => $validated['options'],
            'audience' => $validated['audience'],
            'starts_at' => $validated['starts_at'] ?? now(),
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $validated['is_active'],
            'meta' => [],
        ]);

        return redirect()->route('admin.surveys.edit', $survey->id);
    }

    public function edit(Survey $survey): View
    {
        return view('admin.surveys.form', [
            'title' => 'ویرایش نظرسنجی',
            'survey' => $survey,
        ]);
    }

    public function update(Request $request, Survey $survey): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $survey->forceFill([
            'question' => $validated['question'],
            'options' => $validated['options'],
            'audience' => $validated['audience'],
            'starts_at' => $validated['starts_at'] ?? $survey->starts_at,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $validated['is_active'],
        ])->save();

        return redirect()->route('admin.surveys.edit', $survey->id);
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $survey->delete();

        return redirect()->route('admin.surveys.index');
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'options_raw' => ['required', 'string', 'max:5000'],
            'audience' => ['required', 'string', Rule::in(['all', 'authenticated', 'purchasers'])],
            'starts_at' => ['nullable', 'string', 'max:32'],
            'ends_at' => ['nullable', 'string', 'max:32'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $options = collect(preg_split("/\r\n|\n|\r/", (string) $validated['options_raw']))
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();

        if (count($options) < 2) {
            throw ValidationException::withMessages([
                'options_raw' => ['حداقل دو گزینه لازم است.'],
            ]);
        }

        $startsAt = $this->parseDateTimeOrFail('starts_at', $validated['starts_at'] ?? null);
        $endsAt = $this->parseDateTimeOrFail('ends_at', $validated['ends_at'] ?? null);

        if ($startsAt && $endsAt && $endsAt->lessThan($startsAt)) {
            throw ValidationException::withMessages([
                'ends_at' => ['تاریخ پایان باید بعد از تاریخ شروع باشد.'],
            ]);
        }

        return [
            'question' => $validated['question'],
            'options' => $options,
            'audience' => $validated['audience'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
