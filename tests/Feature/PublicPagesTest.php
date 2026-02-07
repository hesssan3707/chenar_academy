<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_about_page_loads(): void
    {
        $this->get('/about')->assertOk();
    }

    public function test_contact_page_loads(): void
    {
        $this->get('/contact')->assertOk();
    }
}
