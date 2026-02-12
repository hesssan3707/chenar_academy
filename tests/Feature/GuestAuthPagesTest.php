<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAuthPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_cannot_view_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('login'))->assertRedirect(route('panel.library.index'));
    }

    public function test_authenticated_user_cannot_view_register_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('register'))->assertRedirect(route('panel.library.index'));
    }

    public function test_authenticated_user_cannot_view_forgot_password_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('password.forgot'))->assertRedirect(route('panel.library.index'));
    }
}
