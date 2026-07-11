<?php

namespace Tests\Feature;

use Tests\TestCase;

class TicketPrazoViewsTest extends TestCase
{
    public function test_ticket_views_render_prazo_in_the_required_locations()
    {
        $index = file_get_contents(resource_path('views/tickets/index.blade.php'));
        $mine = file_get_contents(resource_path('views/tickets/my.blade.php'));
        $show = file_get_contents(resource_path('views/tickets/show.blade.php'));

        $this->assertStringContainsString('<strong>Prazo:</strong>', $index);
        $this->assertStringNotContainsString('<strong>Origem:</strong>', $index);
        $this->assertStringContainsString('<strong>Prazo:</strong>', $mine);
        $this->assertStringNotContainsString('<strong>Origem:</strong>', $mine);
        $this->assertTrue(strpos($show, '<strong>Prazo:</strong>') > strpos($show, '<strong>Horas Gastas:</strong>'));
        $this->assertStringContainsString("format('d/m/Y')", $index . $mine . $show);
    }
}
