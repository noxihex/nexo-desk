<?php

namespace Tests\Feature;

use App\Support\TicketReturnUrl;
use Illuminate\Http\Request;
use Tests\TestCase;

class TicketReturnUrlTest extends TestCase
{
    /** @test */
    public function it_preserves_allowed_ticket_list_urls_with_query_strings(): void
    {
        $returnUrl = route('tickets.index', [
            'search' => 'impressora',
            'setor_id' => 2,
            'page' => 3,
        ]);
        $request = Request::create('/tickets/10', 'GET', ['return_to' => $returnUrl]);

        $this->assertSame($returnUrl, TicketReturnUrl::resolve($request));
    }

    /** @test */
    public function it_accepts_each_supported_ticket_list_as_an_origin(): void
    {
        foreach (['tickets.index', 'tickets.my', 'tickets.pendentes', 'tickets.cliente.index'] as $routeName) {
            $returnUrl = route($routeName, ['page' => 2]);
            $request = Request::create('/tickets/10', 'GET', ['return_to' => $returnUrl]);

            $this->assertSame($returnUrl, TicketReturnUrl::resolve($request));
        }
    }

    /** @test */
    public function it_rejects_external_or_unrelated_return_urls(): void
    {
        foreach (['https://example.com/tickets', url('/usuarios')] as $unsafeUrl) {
            $request = Request::create('/tickets/10', 'GET', ['return_to' => $unsafeUrl]);

            $this->assertSame(route('tickets.index'), TicketReturnUrl::resolve($request));
        }
    }
}
