<?php

namespace Tests\Feature;

use App\Http\Resources\Api\V2\TicketResource;
use App\Models\Ticket;
use Tests\TestCase;

class TicketWithoutPrazoTest extends TestCase
{
    public function test_historical_prazo_is_hidden_in_legacy_and_v2_serialization()
    {
        $ticket = new Ticket();
        $ticket->setRawAttributes([
            'id' => 123,
            'assunto' => 'Ticket existente',
            'status' => 'pendente analista',
            'origem' => 'Integração legada',
            'prazo' => '2026-07-31',
        ]);

        foreach ([$ticket->toArray(), (new TicketResource($ticket))->resolve()] as $data) {
            $this->assertArrayNotHasKey('prazo', $data);
            $this->assertSame(123, $data['id']);
            $this->assertSame('pendente analista', $data['status']);
            $this->assertSame('Integração legada', $data['origem']);
        }
        $ticket->fill(['prazo' => '2026-08-15', 'assunto' => 'Assunto atualizado']);
        $this->assertSame('2026-07-31', $ticket->getAttributes()['prazo']);
        $this->assertSame('Assunto atualizado', $ticket->assunto);
    }
}
