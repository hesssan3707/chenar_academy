<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSurveyResultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_survey_results_page_with_counts(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $survey = Survey::query()->create([
            'question' => 'سوال تست',
            'options' => ['الف', 'ب'],
            'audience' => 'all',
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
            'meta' => [],
        ]);

        SurveyResponse::query()->create([
            'survey_id' => $survey->id,
            'user_id' => null,
            'anon_token' => 'anon-1',
            'answer' => 'الف',
            'answered_at' => now()->subMinutes(2),
            'meta' => [],
        ]);

        SurveyResponse::query()->create([
            'survey_id' => $survey->id,
            'user_id' => null,
            'anon_token' => 'anon-2',
            'answer' => 'الف',
            'answered_at' => now()->subMinute(),
            'meta' => [],
        ]);

        SurveyResponse::query()->create([
            'survey_id' => $survey->id,
            'user_id' => null,
            'anon_token' => 'anon-3',
            'answer' => 'ب',
            'answered_at' => now(),
            'meta' => [],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.surveys.results', $survey->id))
            ->assertOk()
            ->assertSee('نتایج نظرسنجی')
            ->assertSee('سوال تست')
            ->assertSee('الف')
            ->assertSee('ب')
            ->assertSee('3');
    }
}
