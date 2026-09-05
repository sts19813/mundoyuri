<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationViewTest extends TestCase
{
    public function test_application_uses_bootstrap_pagination_views(): void
    {
        $paginator = new LengthAwarePaginator(
            range(1, 10),
            20,
            10,
            1,
            ['path' => '/prueba'],
        );

        $html = (string) $paginator->links();

        $this->assertStringContainsString('class="pagination"', $html);
        $this->assertStringContainsString('class="page-link"', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }
}
