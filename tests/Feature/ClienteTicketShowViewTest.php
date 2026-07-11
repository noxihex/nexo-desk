<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClienteTicketShowViewTest extends TestCase
{
    public function test_cliente_ticket_show_uses_layout_javascript_without_reloading_jquery_or_bootstrap()
    {
        $view = file_get_contents(resource_path('views/tickets/cliente/show.blade.php'));

        $this->assertStringContainsString("@extends('adminlte::page')", $view);
        $this->assertStringContainsString('btn btn-primary btn-sm d-none', $view);
        $this->assertStringContainsString("$('#finalizeModal').on('show.bs.modal'", $view);
        $this->assertStringContainsString('function addAttachmentField()', $view);
        $this->assertStringContainsString('toastr.min.js', $view);
        $this->assertStringNotContainsString('cdnjs.cloudflare.com/ajax/libs/jquery', $view);
        $this->assertStringNotContainsString('stackpath.bootstrapcdn.com/bootstrap', $view);
    }
}
