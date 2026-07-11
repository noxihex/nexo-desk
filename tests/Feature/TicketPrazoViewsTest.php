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
        $this->assertStringContainsString("format('d/m')", $index);
        $this->assertStringNotContainsString('<strong>Contato:</strong>', $index);
        $this->assertStringNotContainsString('<strong>Modificado:</strong>', $index);
        $this->assertStringNotContainsString('<strong>Grupo:</strong>', $index);
        $this->assertStringNotContainsString('<strong>Atribuído ao Analista:</strong>', $index);
        $this->assertStringNotContainsString('<strong>SLA:</strong>', $index);
        $this->assertStringContainsString('ticket-list-card__actions', $index);
        $this->assertStringContainsString('<strong>Prazo:</strong>', $mine);
        $this->assertStringNotContainsString('<strong>Origem:</strong>', $mine);
        $this->assertTrue(strpos($show, '<strong>Prazo:</strong>') > strpos($show, '<strong>Horas Gastas:</strong>'));
        $this->assertStringContainsString('<strong>Modificado:</strong>', $show);
        $this->assertStringContainsString("format('d/m/Y')", $index . $mine . $show);
    }
}
