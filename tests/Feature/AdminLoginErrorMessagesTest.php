<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginErrorMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_returns_phone_not_found_message(): void
    {
        $this->post(route('admin.login.store'), [
            'action' => 'login_password',
            'phone' => '09123456789',
            'password' => 'password123',
        ])->assertSessionHasErrors(['phone' => 'شماره موبایل یافت نشد.']);
    }

    public function test_admin_login_returns_incorrect_password_message(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $user->roles()->attach($adminRole->id);

        $this->post(route('admin.login.store'), [
            'action' => 'login_password',
            'phone' => $user->phone,
            'password' => 'wrong123',
        ])->assertSessionHasErrors(['password' => 'رمز عبور اشتباه است.']);
    }
}
