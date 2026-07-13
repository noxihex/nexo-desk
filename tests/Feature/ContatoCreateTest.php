<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContatoCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_criacao_de_contato_define_cliente_helpdesk_sem_role_no_request(): void
    {
        Role::findOrCreate('supervisor', 'web');
        Role::findOrCreate('cliente', 'web');

        $supervisor = User::factory()->create(['status' => true]);
        $supervisor->assignRole('supervisor');
        $empresa = Empresa::create([
            'nome' => 'Empresa de Teste',
            'cnpj' => '00000000000001',
        ]);

        $this->actingAs($supervisor)
            ->post(route('clientes.store'), [
                'name' => 'Novo Contato',
                'email' => 'contato@example.com',
                'password' => 'senha-segura',
                'password_confirmation' => 'senha-segura',
                'empresa_id' => $empresa->id,
                'role' => 'clientedc',
            ])
            ->assertRedirect(route('empresas.edit', $empresa));

        $contato = User::where('email', 'contato@example.com')->firstOrFail();

        $this->assertSame($empresa->id, $contato->empresa_id);
        $this->assertTrue($contato->hasRole('cliente'));
        $this->assertFalse($contato->hasRole('clientedc'));
    }

    public function test_formulario_exibe_empresa_sem_caixas_de_selecao(): void
    {
        Role::findOrCreate('supervisor', 'web');

        $supervisor = User::factory()->create(['status' => true]);
        $supervisor->assignRole('supervisor');
        $empresa = Empresa::create([
            'nome' => 'Empresa de Teste',
            'cnpj' => '00000000000001',
        ]);

        $this->actingAs($supervisor)
            ->get(route('clientes.create', ['empresa_id' => $empresa->id]))
            ->assertOk()
            ->assertSeeText('Empresa de Teste')
            ->assertDontSeeText('Tipo de Central do Cliente')
            ->assertDontSee('name="role"', false)
            ->assertDontSee('name="empresa_id_disabled"', false)
            ->assertSee('name="empresa_id" value="' . $empresa->id . '"', false);
    }

    public function test_edicao_de_contato_exibe_empresa_sem_selecao_e_mantem_role_cliente(): void
    {
        Role::findOrCreate('supervisor', 'web');
        Role::findOrCreate('cliente', 'web');
        Role::findOrCreate('clientedc', 'web');

        $supervisor = User::factory()->create(['status' => true]);
        $supervisor->assignRole('supervisor');
        $empresa = Empresa::create([
            'nome' => 'Empresa de Teste',
            'cnpj' => '00000000000001',
        ]);
        $outraEmpresa = Empresa::create([
            'nome' => 'Outra Empresa',
            'cnpj' => '00000000000002',
        ]);
        $contato = User::factory()->create([
            'empresa_id' => $empresa->id,
            'status' => true,
        ]);
        $contato->assignRole('clientedc');

        $this->actingAs($supervisor)
            ->get(route('clientes.edit', $contato))
            ->assertOk()
            ->assertSeeText('Empresa de Teste')
            ->assertDontSeeText('Tipo de Central do Cliente')
            ->assertDontSee('name="role"', false)
            ->assertDontSee('<select name="empresa_id"', false)
            ->assertSee('name="empresa_id" value="' . $empresa->id . '"', false);

        $this->actingAs($supervisor)
            ->put(route('clientes.update', $contato), [
                'name' => 'Contato Atualizado',
                'email' => $contato->email,
                'empresa_id' => $outraEmpresa->id,
                'role' => 'clientedc',
            ])
            ->assertRedirect(route('empresas.edit', $empresa));

        $contato->refresh();

        $this->assertSame('Contato Atualizado', $contato->name);
        $this->assertSame($empresa->id, $contato->empresa_id);
        $this->assertTrue($contato->hasRole('cliente'));
        $this->assertFalse($contato->hasRole('clientedc'));
    }
}
