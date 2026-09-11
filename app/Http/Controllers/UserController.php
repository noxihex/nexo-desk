<?php

namespace App\Http\Controllers;

use App\Actions\Account\UpdateAccount;
use App\Actions\Cadastros\ManagePeople;
use App\Actions\Cadastros\SavePerson;
use App\Actions\Cadastros\ChangePersonStatus;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Exibe a lista de usuários.
     */
    public function index(Request $request)
    {
        return view('cadastros.usuarios.index');
    }

    /**
     * Exibe o formulário de criação de um novo usuário.
     */
    public function create()
    {
        return view('cadastros.usuarios.create');
    }

    /**
     * Armazena um novo usuário no banco de dados.
     */
    public function store(Request $request)
    {
        app(SavePerson::class)->handle($request->all(), false);
        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um usuário existente.
     */
    public function edit(User $user)
    {
        $this->authorizeTeamUserManagement($user);
        return view('cadastros.usuarios.edit', compact('user'));
    }

    /**
     * Atualiza um usuário existente.
     */
    public function update(Request $request, User $user)
    {
        app(SavePerson::class)->handle($request->all(), false, $user->id);
        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Desativa um usuário (alterando o status para inativo).
     */

    public function deactivate(User $user)
    {
        $this->authorizeTeamUserManagement($user);
        if ($user->id === 1) {
            return redirect()->route('usuarios.index')->with('error', 'Este usuário não pode ser desativado.');
        }
        $user = app(ChangePersonStatus::class)->handle($user->id, false);
        return redirect()->route('usuarios.index')->with('success', $user->status ? 'Usuário ativado com sucesso!' : 'Usuário desativado com sucesso!');
    }

    public function indexClientes()
    {
        return redirect()->route('empresas.index');
    }

    public function createCliente(Request $request)
    {
        if (! $request->filled('empresa_id')) {
            return redirect()->route('empresas.index')->with('error', 'Selecione uma empresa para cadastrar seu contato.');
        }
        $request->validate(['empresa_id' => 'required|integer|exists:empresas,id']);
        $empresaId = (int) $request->query('empresa_id');
        return view('cadastros.clientes.create', compact('empresaId'));
    }

    public function storeCliente(Request $request)
    {
        $user = app(SavePerson::class)->handle($request->all(), true);
        return ($user->empresa_id ? redirect()->route('empresas.edit', $user->empresa_id) : redirect()->route('clientes.index'))->with('success', 'Contato criado com sucesso!');
    }

    public function editCliente(User $user)
    {
        $this->authorizeClientManagement($user);
        return view('cadastros.clientes.edit', compact('user'));
    }

    public function updateCliente(Request $request, User $user)
    {
        $user = app(SavePerson::class)->handle($request->all(), true, $user->id);
        return ($user->empresa_id ? redirect()->route('empresas.edit', $user->empresa_id) : redirect()->route('clientes.index'))->with('success', 'Contato atualizado com sucesso!');
    }


    public function deactivateCliente(User $user)
    {
        $user = app(ChangePersonStatus::class)->handle($user->id, true);
        return ($user->empresa_id ? redirect()->route('empresas.edit', $user->empresa_id) : redirect()->route('clientes.index'))->with('success', $user->status ? 'Contato ativado com sucesso!' : 'Contato desativado com sucesso!');
    }


public function editMinhaConta()
{
    $user = auth()->user()->load('notificationPreference'); // Pega o usuário autenticado
    return view('minhaconta.edit', compact('user')); // Retorna a view de edição de conta
}

public function updateMinhaConta(Request $request)
{
    app(UpdateAccount::class)->profile($request->all());

    return redirect()->route('minhaconta.edit')->with('success', 'Perfil atualizado com sucesso!');
}


public function updateMinhaSenha(Request $request)
{
    if (! app(UpdateAccount::class)->password($request->all())) {
        return redirect()->route('minhaconta.edit')->with('error', 'A senha atual está incorreta.');
    }

    return redirect()->route('minhaconta.edit')->with('success', 'Senha alterada com sucesso!');
}

public function getEmpresa($id)
{
    $user = User::with('empresa')->find($id);

    if ($user && $user->empresa) {
        return response()->json(['empresa_id' => $user->empresa->id, 'empresa_nome' => $user->empresa->nome]);
    }

    return response()->json(['empresa_id' => null, 'empresa_nome' => null]);
}

    private function authorizeTeamUserManagement(User $user): void
    {
        app(ManagePeople::class)->authorize($user, false);
    }

    private function authorizeClientManagement(User $user): void
    {
        app(ManagePeople::class)->authorize($user, true);
    }


}
