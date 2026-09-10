<?php

namespace Tests\Feature;

use App\Livewire\Modern\ClientTickets\ClientTicketIndex;
use App\Models\Empresa;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClienteTicketSearchTest extends TestCase
{
    use RefreshDatabase;

    private function cliente(Empresa $empresa): User
    {
        $cliente = User::factory()->create([
            'empresa_id' => $empresa->id,
            'status' => true,
        ]);

        Role::findOrCreate('cliente', 'web');
        $cliente->assignRole('cliente');

        return $cliente;
    }

    private function empresa(string $nome): Empresa
    {
        return Empresa::create([
            'nome' => $nome,
            'cnpj' => str_pad((string) Empresa::count() + 1, 14, '0', STR_PAD_LEFT),
        ]);
    }

    private function ticket(User $cliente, Empresa $empresa, string $assunto): Ticket
    {
        return Ticket::create([
            'assunto' => $assunto,
            'descricao' => 'Descrição de teste',
            'user_id' => $cliente->id,
            'cliente_id' => $cliente->id,
            'empresa_id' => $empresa->id,
            'status' => 'aberto',
        ]);
    }

    public function test_cliente_pesquisa_proprio_ticket_por_id_e_assunto()
    {
        $empresa = $this->empresa('Empresa A');
        $cliente = $this->cliente($empresa);
        $ticket = $this->ticket($cliente, $empresa, 'Falha no acesso VPN');

        $this->actingAs($cliente)
            ->get(route('tickets.cliente.index', ['search' => $ticket->id]))
            ->assertOk()
            ->assertSee("Ticket #{$ticket->id}")
            ->assertSee($ticket->assunto);

        $this->actingAs($cliente)
            ->get(route('tickets.cliente.index', ['search' => 'acesso VPN']))
            ->assertOk()
            ->assertSee("Ticket #{$ticket->id}")
            ->assertSee($ticket->assunto);
    }

    public function test_cliente_pesquisa_tickets_de_outro_cliente_da_mesma_empresa_na_visao_empresa()
    {
        $empresa = $this->empresa('Empresa A');
        $cliente = $this->cliente($empresa);
        $outroCliente = $this->cliente($empresa);
        $ticket = $this->ticket($outroCliente, $empresa, 'Impressora indisponível');

        $this->actingAs($cliente)
            ->get(route('tickets.cliente.index', [
                'viewCompanyTickets' => 1,
                'search' => 'Impressora',
            ]))
            ->assertOk()
            ->assertSee("Ticket #{$ticket->id}")
            ->assertSee($ticket->assunto);
    }

    public function test_pesquisa_do_cliente_nunca_retorna_ticket_de_outra_empresa()
    {
        $empresa = $this->empresa('Empresa A');
        $outraEmpresa = $this->empresa('Empresa B');
        $cliente = $this->cliente($empresa);
        $clienteExterno = $this->cliente($outraEmpresa);
        $ticketExterno = $this->ticket($clienteExterno, $outraEmpresa, 'Termo exclusivo - Empresa externa');
        $ticketInconsistente = $this->ticket($cliente, $outraEmpresa, 'Termo exclusivo - Empresa inconsistente');

        $response = $this->actingAs($cliente)
            ->get(route('tickets.cliente.index', [
                'viewCompanyTickets' => 1,
                'search' => 'Termo exclusivo',
            ]));

        $response->assertOk()
            ->assertDontSee("Ticket #{$ticketExterno->id}")
            ->assertDontSee('Empresa externa')
            ->assertDontSee("Ticket #{$ticketInconsistente->id}")
            ->assertDontSee('Empresa inconsistente');
    }

    public function test_pesquisa_e_visao_selecionada_sao_preservadas_na_tela()
    {
        $empresa = $this->empresa('Empresa A');
        $cliente = $this->cliente($empresa);
        for ($i = 1; $i <= 11; $i++) {
            $this->ticket($cliente, $empresa, "Consulta persistida {$i}");
        }

        $this->actingAs($cliente);

        Livewire::withQueryParams([
                'viewCompanyTickets' => 1,
                'search' => 'Consulta',
            ])
            ->test(ClientTicketIndex::class)
            ->assertSet('search', 'Consulta')
            ->assertSet('viewCompanyTickets', true)
            ->assertSee('Ver somente meus tickets')
            ->assertSee('Consulta persistida')
            ->call('nextPage')
            ->assertSet('paginators.page', 2)
            ->assertSet('search', 'Consulta')
            ->assertSet('viewCompanyTickets', true);
    }
}
