<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_access_user_panel_routes(): void
    {
        $this->get('/panel')->assertRedirect(route('login'));
    }

    public function test_guest_can_view_admin_login_page(): void
    {
        $this->get(route('admin.login'))->assertOk();
    }

    public function test_admin_can_login_from_admin_login_page(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->post(route('admin.login.store'), [
            'action' => 'login_password',
            'phone' => $user->phone,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_regular_user_cannot_login_from_admin_login_page(): void
    {
        $user = User::factory()->create();

        $this->post(route('admin.login.store'), [
            'action' => 'login_password',
            'phone' => $user->phone,
            'password' => 'password',
        ])->assertSessionHasErrors(['phone']);
    }

    public function test_guest_is_told_login_is_required_for_protected_routes(): void
    {
        $this->get('/panel')
            ->assertRedirect(route('login'))
            ->assertSessionHas('toast');
    }

    public function test_regular_user_can_access_user_panel_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/panel')->assertOk();
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_admin_can_update_about_page_content(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'about_title' => 'درباره ما',
                'about_subtitle' => 'معرفی کوتاه',
                'about_body' => 'متن درباره ما',
            ])->assertRedirect(route('admin.settings.index'));

        $setting = Setting::query()->where('key', 'page.about')->first();
        $this->assertNotNull($setting);
        $this->assertSame('pages', $setting->group);
        $this->assertIsArray($setting->value);
        $this->assertSame('درباره ما', $setting->value['title']);
        $this->assertSame('معرفی کوتاه', $setting->value['subtitle']);
        $this->assertSame('متن درباره ما', $setting->value['body']);
    }

    public function test_admin_can_update_tax_percent_setting(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'tax_percent' => 9,
            ])->assertRedirect(route('admin.settings.index'));

        $setting = Setting::query()->where('key', 'commerce.tax_percent')->first();
        $this->assertNotNull($setting);
        $this->assertSame('commerce', $setting->group);
        $this->assertSame(9, $setting->value);
    }

    public function test_admin_can_update_card_to_card_cards_settings(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        config()->set('theme.available', ['default']);
        config()->set('theme.default', 'default');
        config()->set('theme.setting_key', 'theme.active');

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'theme' => 'default',
                'card_to_card_card1_name' => 'چنار آکادمی',
                'card_to_card_card1_number' => '۶۰۳۷-۹۹۱۸-۱۲۳۴-۵۶۷۸',
                'card_to_card_card2_name' => 'حساب پشتیبان',
                'card_to_card_card2_number' => '6037 9918 1111 2222',
            ])->assertRedirect(route('admin.settings.index'));

        $card1Name = Setting::query()->where('key', 'commerce.card_to_card.card1.name')->first();
        $this->assertNotNull($card1Name);
        $this->assertSame('commerce', $card1Name->group);
        $this->assertSame('چنار آکادمی', $card1Name->value);

        $card1Number = Setting::query()->where('key', 'commerce.card_to_card.card1.number')->first();
        $this->assertNotNull($card1Number);
        $this->assertSame('commerce', $card1Number->group);
        $this->assertSame('6037991812345678', $card1Number->value);

        $card2Name = Setting::query()->where('key', 'commerce.card_to_card.card2.name')->first();
        $this->assertNotNull($card2Name);
        $this->assertSame('commerce', $card2Name->group);
        $this->assertSame('حساب پشتیبان', $card2Name->value);

        $card2Number = Setting::query()->where('key', 'commerce.card_to_card.card2.number')->first();
        $this->assertNotNull($card2Number);
        $this->assertSame('commerce', $card2Number->group);
        $this->assertSame('6037991811112222', $card2Number->value);
    }

    public function test_admin_can_access_user_panel_routes(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->actingAs($user)->get('/panel')->assertOk();
    }

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'oldpass123',
        ]);

        $this->actingAs($user)
            ->put(route('panel.profile.password.update'), [
                'current_password' => 'oldpass123',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])->assertRedirect();

        $this->assertTrue(Hash::check('newpass123', $user->refresh()->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'oldpass123',
        ]);

        $this->actingAs($user)
            ->put(route('panel.profile.password.update'), [
                'current_password' => 'wrongpass',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])->assertSessionHasErrors(['current_password']);
    }
}
