<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private int $initialOutputBufferLevel = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initialOutputBufferLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->initialOutputBufferLevel) {
            ob_end_clean();
        }

        parent::tearDown();
    }
}
