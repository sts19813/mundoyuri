<?php

namespace Tests\Feature;

use App\Services\RichContentService;
use Tests\TestCase;

class RichContentSecurityTest extends TestCase
{
    public function test_nested_disallowed_elements_cannot_hide_unsafe_content(): void
    {
        $service = app(RichContentService::class);
        $html = '<p><unknown><unknown><strong>Texto seguro</strong><img src="/storage/community-post-images/demo.webp" onerror="alert(1)"><a href="javascript:alert(2)">enlace</a><script>alert(3)</script></unknown></unknown></p>';

        $clean = $service->sanitize($html);

        $this->assertStringContainsString('<strong>Texto seguro</strong>', $clean);
        $this->assertStringContainsString('/storage/community-post-images/demo.webp', $clean);
        $this->assertStringContainsString('enlace', $clean);
        $this->assertStringNotContainsString('unknown', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('alert(', $clean);
    }

    public function test_previously_saved_nested_content_is_sanitized_when_rendered(): void
    {
        $html = '<p><unknown><unknown><img src="x" onerror="alert(1)"></unknown></unknown></p>';
        $rendered = app(RichContentService::class)->renderProfileBiography($html)->toHtml();

        $this->assertStringNotContainsString('onerror', $rendered);
        $this->assertStringNotContainsString('alert(', $rendered);
    }
}
