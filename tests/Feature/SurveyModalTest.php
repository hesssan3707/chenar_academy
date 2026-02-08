<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_active_survey_and_can_submit_and_it_wont_show_again(): void
    {
        $survey = Survey::query()->create([
            'question' => 'سوال تست',
            'options' => ['گزینه ۱', 'گزینه ۲'],
            'audience' => 'all',
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
            'meta' => [],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('سوال تست');

        $post = $this->post(route('surveys.responses.store', $survey->id), [
            'answer' => 'گزینه ۲',
            'redirect_to' => url('/'),
        ])->assertRedirect('/');

        $cookie = collect($post->headers->getCookies())->first(fn ($item) => $item->getName() === 'survey_anon_token');
        $this->assertNotNull($cookie);
        $this->assertSame('survey_anon_token', $cookie->getName());
        $this->assertNotSame('', (string) $cookie->getValue());

        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'answer' => 'گزینه ۲',
        ]);

        $this->withCookie($cookie->getName(), $cookie->getValue())
            ->get('/')
            ->assertOk()
            ->assertDontSee('سوال تست');
    }

    public function test_survey_is_not_visible_outside_display_period(): void
    {
        Survey::query()->create([
            'question' => 'سوال آینده',
            'options' => ['الف', 'ب'],
            'audience' => 'all',
            'starts_at' => now()->addHour(),
            'ends_at' => null,
            'is_active' => true,
            'meta' => [],
        ]);

        Survey::query()->create([
            'question' => 'سوال گذشته',
            'options' => ['الف', 'ب'],
            'audience' => 'all',
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subDay(),
            'is_active' => true,
            'meta' => [],
        ]);

        $this->get('/')->assertOk()->assertDontSee('سوال آینده')->assertDontSee('سوال گذشته');
    }

    public function test_authenticated_audience_shows_only_for_logged_in_users(): void
    {
        Survey::query()->create([
            'question' => 'فقط ورود کرده',
            'options' => ['۱', '۲'],
            'audience' => 'authenticated',
            'starts_at' => now()->subMinute(),
            'ends_at' => null,
            'is_active' => true,
            'meta' => [],
        ]);

        $this->get('/')->assertOk()->assertDontSee('فقط ورود کرده');

        $user = User::factory()->create();
        $this->actingAs($user)->get('/')->assertOk()->assertSee('فقط ورود کرده');
    }

    public function test_purchasers_audience_shows_only_for_users_with_any_purchase(): void
    {
        $survey = Survey::query()->create([
            'question' => 'فقط خریداران',
            'options' => ['الف', 'ب'],
            'audience' => 'purchasers',
            'starts_at' => now()->subMinute(),
            'ends_at' => null,
            'is_active' => true,
            'meta' => [],
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/')->assertOk()->assertDontSee('فقط خریداران');

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'محصول تست',
            'slug' => 'survey-test-product',
            'status' => 'published',
            'base_price' => 1000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        ProductAccess::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => null,
            'granted_at' => now(),
            'expires_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($user)->get('/')->assertOk()->assertSee('فقط خریداران');

        SurveyResponse::query()->create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'anon_token' => null,
            'answer' => 'الف',
            'answered_at' => now(),
            'meta' => [],
        ]);

        $this->actingAs($user)->get('/')->assertOk()->assertDontSee('فقط خریداران');
    }
}
