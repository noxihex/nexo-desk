<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClienteTicketIndexViewTest extends TestCase
{
    public function test_cliente_ticket_index_is_a_modern_livewire_entry(): void
    {
        $view = file_get_contents(resource_path('views/tickets/cliente/index.blade.php'));

        $this->assertStringContainsString('<x-modern.client-tickets.layout', $view);
        $this->assertStringContainsString('<livewire:modern.client-tickets.client-ticket-index', $view);
        $this->assertStringNotContainsString("@extends('adminlte::page')", $view);
    }
}
