<?php

namespace Tests\Feature;

use Tests\TestCase;

class TicketPrazoViewsTest extends TestCase
{
    public function test_ticket_views_omit_prazo_and_preserve_other_details()
    {
        $index = file_get_contents(resource_path('views/tickets/index.blade.php'));
        $mine = file_get_contents(resource_path('views/tickets/my.blade.php'));
        $listing = file_get_contents(resource_path('views/livewire/modern/tickets/ticket-index.blade.php'));
        $show = file_get_contents(resource_path('views/livewire/modern/tickets/ticket-show.blade.php'));

        $this->assertStringContainsString('livewire:modern.tickets.ticket-index', $index);
        $this->assertStringContainsString('livewire:modern.tickets.ticket-index', $mine);
        $this->assertStringNotContainsString('Prazo', $listing);
        $this->assertStringNotContainsString('Origem', $listing);
        $this->assertStringContainsString("'d/m/Y - H:i'", $listing);
        $this->assertStringContainsString('<details class="group', $listing);
        $this->assertStringContainsString('Filtros adicionais', $listing);
        $this->assertStringContainsString('>Buscar</x-modern.button>', $listing);
        $this->assertStringNotContainsString('Buscar e filtrar', $listing);
        $this->assertStringContainsString('variant="filled" color="sky"', $listing);
        $this->assertStringContainsString('variant="filled" color="amber"', $listing);
        $this->assertStringNotContainsString('Contato', $listing);
        $this->assertStringNotContainsString('Modificado:', $listing);
        $this->assertStringNotContainsString('Grupo', $listing);
        $this->assertStringNotContainsString('Atribuído ao Analista', $listing);
        $this->assertStringNotContainsString('<dt class="font-medium text-zinc-500 dark:text-zinc-400">SLA</dt>', $listing);
        $this->assertStringNotContainsString('<strong>Prazo:</strong>', $show);
        $this->assertStringContainsString('Horas gastas', $show);
        $this->assertStringContainsString('Atualizado em', $show);
    }
}
