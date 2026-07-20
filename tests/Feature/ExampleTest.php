<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertDontSee('BG.mp4', false)
            ->assertDontSee('youtube.com/embed', false)
            ->assertDontSee('googlevideo.com', false)
            ->assertSee('youtube-nocookie.com/embed/3Q7eEPBE5ZI', false)
            ->assertSee('controls=0', false);
    }
}
