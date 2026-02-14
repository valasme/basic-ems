<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    public function test_welcome_page_displays_core_content(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('BasicEMS');
        $response->assertSee('Modern EMS');
    }
}
