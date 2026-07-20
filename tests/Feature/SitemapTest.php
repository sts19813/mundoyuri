<?php

namespace Tests\Feature;

use Tests\TestCase;

class SitemapTest extends TestCase
{
    public function test_sitemap_is_valid_xml_and_contains_public_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee(route('home'), false)
            ->assertSee(route('catalog.series.index'), false)
            ->assertDontSee('/admin/', false);
    }
}
