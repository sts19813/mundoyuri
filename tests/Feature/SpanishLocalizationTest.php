<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpanishLocalizationTest extends TestCase
{
    public function test_system_locale_translates_relative_times_and_pagination(): void
    {
        app()->setLocale('es');

        $this->assertSame('es', app()->getLocale());
        $this->assertStringContainsString('semana', now()->subWeek()->diffForHumans());
        $this->assertSame('&laquo; Anterior', __('pagination.previous'));
        $this->assertSame('Siguiente &raquo;', __('pagination.next'));
    }
}
