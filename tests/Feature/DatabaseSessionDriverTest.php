<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSessionDriverTest extends TestCase
{
    use RefreshDatabase;

    public function test_sessions_are_persisted_in_database_when_database_driver_is_enabled(): void
    {
        config()->set('session.driver', 'database');

        $this->get(route('home'))->assertOk();

        $this->assertDatabaseCount('sessions', 1);
    }
}
