<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClienteTicketIndexViewTest extends TestCase
{
    public function test_cliente_ticket_index_scopes_link_spacing_to_page_content()
    {
        $view = file_get_contents(resource_path('views/tickets/cliente/index.blade.php'));

        $this->assertStringContainsString('.content a {', $view);
        $this->assertStringNotContainsString("        a {\n            margin: 2px;", $view);
    }
}
