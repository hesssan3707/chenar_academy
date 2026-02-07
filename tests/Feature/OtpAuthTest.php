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

        $this->postJson(route('otp.send'), [
            'purpose' => 'login',
            'phone' => $phone,
        ])->assertOk()->assertJson([
            'ok' => true,
            'purpose' => 'login',
            'phone' => $phone,
            'cooldown_seconds' => 120,
        ]);

        $otp = OtpCode::query()->where('phone', $phone)->where('purpose', 'login')->firstOrFail();
        $this->assertTrue(Hash::check('11111', $otp->code_hash));
    }

    public function test_send_otp_is_rate_limited_for_two_minutes(): void
    {
        $phone = '09120000010';

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
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => $phone,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'otp_code' => '11111',
        ])->assertRedirect(route('panel.dashboard'));

        $this->assertDatabaseHas('users', [
            'phone' => $phone,
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
