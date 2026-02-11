<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OtpAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_login_otp_stores_code_in_database(): void
    {
        $phone = '09120000009';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->postJson(route('otp.send'), [
            'purpose' => 'login',
            'phone' => $phone,
        ])->assertOk()->assertJson([
            'ok' => true,
            'purpose' => 'login',
            'phone' => $phone,
            'cooldown_seconds' => 60,
        ]);

        $otp = OtpCode::query()->where('phone', $phone)->where('purpose', 'login')->firstOrFail();
        $this->assertTrue(Hash::check('11111', $otp->code_hash));
    }

    public function test_send_admin_login_otp_stores_code_in_database_for_admin_user(): void
    {
        $phone = '09120000015';

        $user = User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $adminRole = \App\Models\Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->postJson(route('admin.otp.send'), [
            'purpose' => 'admin_login',
            'phone' => $phone,
        ])->assertOk()->assertJson([
            'ok' => true,
            'purpose' => 'admin_login',
            'phone' => $phone,
            'cooldown_seconds' => 60,
        ]);

        $otp = OtpCode::query()->where('phone', $phone)->where('purpose', 'admin_login')->firstOrFail();
        $this->assertTrue(Hash::check('11111', $otp->code_hash));
    }

    public function test_send_admin_login_otp_requires_admin_user(): void
    {
        $phone = '09120000016';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->postJson(route('admin.otp.send'), [
            'purpose' => 'admin_login',
            'phone' => $phone,
        ])->assertStatus(422)->assertJsonValidationErrors(['phone']);
    }

    public function test_login_otp_send_requires_existing_user(): void
    {
        $phone = '09120000011';

        $this->postJson(route('otp.send'), [
            'purpose' => 'login',
            'phone' => $phone,
        ])->assertStatus(422)->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseCount('otp_codes', 0);
    }

    public function test_send_otp_is_rate_limited_to_two_per_minute(): void
    {
        $phone = '09120000010';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->postJson(route('otp.send'), [
            'purpose' => 'login',
            'phone' => $phone,
        ])->assertOk();

        $this->postJson(route('otp.send'), [
            'purpose' => 'login',
            'phone' => $phone,
        ])->assertOk();

        $this->postJson(route('otp.send'), [
            'purpose' => 'login',
            'phone' => $phone,
        ])->assertStatus(422)->assertJsonValidationErrors(['otp_code']);
    }

    public function test_register_requires_otp_and_creates_user(): void
    {
        $phone = '09120000008';

        $this->postJson(route('otp.send'), [
            'purpose' => 'register',
            'phone' => $phone,
        ])->assertOk();

        $this->post(route('register.store'), [
            'name' => 'Test User',
            'phone' => $phone,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'otp_code' => '11111',
        ])->assertRedirect(route('panel.dashboard'));

        $this->assertDatabaseHas('users', [
            'phone' => $phone,
            'email' => null,
            'is_active' => 1,
        ]);
    }

    public function test_login_with_password_works_with_phone_username(): void
    {
        $phone = '09120000007';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'action' => 'login_password',
            'phone' => $phone,
            'password' => 'password',
        ])->assertRedirect(route('panel.dashboard'));
    }

    public function test_login_password_returns_json_redirect_for_ajax_requests(): void
    {
        $phone = '09120000012';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->postJson(route('login.store'), [
            'action' => 'login_password',
            'phone' => $phone,
            'password' => 'password',
        ])->assertOk()->assertJson([
            'ok' => true,
            'redirect_to' => route('panel.dashboard'),
        ]);
    }

    public function test_login_password_returns_validation_errors_for_bad_credentials_in_ajax_requests(): void
    {
        $phone = '09120000013';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->postJson(route('login.store'), [
            'action' => 'login_password',
            'phone' => $phone,
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors(['phone']);
    }

    public function test_login_password_requires_password_field(): void
    {
        $phone = '09120000004';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'action' => 'login_password',
            'phone' => $phone,
        ])->assertSessionHasErrors([
            'password' => 'فیلد رمز عبور الزامی است.',
        ]);
    }

    public function test_login_with_otp_works(): void
    {
        $phone = '09120000006';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->postJson(route('otp.send'), [
            'purpose' => 'login',
            'phone' => $phone,
        ])->assertOk();

        $this->post(route('login.store'), [
            'action' => 'login_otp',
            'phone' => $phone,
            'otp_code' => '11111',
        ])->assertRedirect(route('panel.dashboard'));
    }

    public function test_login_otp_returns_json_redirect_for_ajax_requests(): void
    {
        $phone = '09120000014';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->postJson(route('otp.send'), [
            'purpose' => 'login',
            'phone' => $phone,
        ])->assertOk();

        $this->postJson(route('login.store'), [
            'action' => 'login_otp',
            'phone' => $phone,
            'otp_code' => '11111',
        ])->assertOk()->assertJson([
            'ok' => true,
            'redirect_to' => route('panel.dashboard'),
        ]);
    }

    public function test_login_otp_requires_otp_code_field(): void
    {
        $phone = '09120000003';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'action' => 'login_otp',
            'phone' => $phone,
        ])->assertSessionHasErrors(['otp_code']);
    }

    public function test_forgot_password_resets_password_with_otp(): void
    {
        $phone = '09120000005';

        User::query()->create([
            'name' => $phone,
            'email' => $phone.'@chenar.local',
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => 'oldpass123',
            'is_active' => true,
        ]);

        $this->postJson(route('otp.send'), [
            'purpose' => 'password_reset',
            'phone' => $phone,
        ])->assertOk();

        $this->post(route('password.forgot.store'), [
            'phone' => $phone,
            'otp_code' => '11111',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertRedirect(route('panel.dashboard'));

        $this->post(route('login.store'), [
            'action' => 'login_password',
            'phone' => $phone,
            'password' => 'newpass123',
        ])->assertRedirect(route('panel.dashboard'));
    }
}
