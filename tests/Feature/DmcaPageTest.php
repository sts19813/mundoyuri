<?php

namespace Tests\Feature;

use Tests\TestCase;

class DmcaPageTest extends TestCase
{
    public function test_the_copyright_notice_page_is_public_and_contains_the_required_process(): void
    {
        $this->get(route('dmca'))
            ->assertOk()
            ->assertSee('admin@mundoyuri.com')
            ->assertSee('Cómo enviar un aviso de retiro')
            ->assertSee('Contra-aviso y restauración')
            ->assertSee('24 a 48 horas hábiles');
    }
}
