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
            ->assertSee('Mundo Yuri: series GL y Girls’ Love online', false)
            ->assertSee('assets/img/social/mundo-yuri-og.jpg', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:height" content="630">', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"alternateName":"Mundo Yuri GL"', false)
            ->assertSee('youtube-nocookie.com/embed/3Q7eEPBE5ZI', false)
            ->assertSee('controls=0', false)
            ->assertSee('<nav class="gl-nav" id="navbar">', false)
            ->assertDontSee('gl-nav scrolled', false);
    }
}
