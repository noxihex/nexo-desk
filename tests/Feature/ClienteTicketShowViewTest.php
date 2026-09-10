<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClienteTicketShowViewTest extends TestCase
{
    public function test_cliente_ticket_show_is_a_modern_livewire_entry(): void
    {
        $view = file_get_contents(resource_path('views/tickets/cliente/show.blade.php'));

        $this->assertStringContainsString('<x-modern.client-tickets.layout', $view);
        $this->assertStringContainsString('<livewire:modern.client-tickets.client-ticket-show', $view);
        $this->assertStringNotContainsString("@extends('adminlte::page')", $view);
        $this->assertStringNotContainsString('jquery', strtolower($view));
        $this->assertStringNotContainsString('bootstrap', strtolower($view));
    }
}
