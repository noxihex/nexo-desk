<?php

namespace App\Http\Controllers;

use App\Models\Setor;
use App\Models\Empresa; // Importando o modelo Empresa
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Exibe a lista de usuários.
     */
    public function index(Request $request)
    {
        $showAll = $request->boolean('todos');

        $users = User::role(['analista', 'supervisor', 'administrador'])
            ->with(['setor', 'empresa', 'roles'])
            ->when(! $showAll, function ($query) {
                $query->where('status', true);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('search') . '%');
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('cadastros.usuarios.index', compact('users', 'showAll'));
    }

    /**
     * Exibe o formulário de criação de um novo usuário.
     */
    public function create()
    {
        $setores = Setor::all(); // Busca todos os setores do banco
        $empresas = Empresa::all(); // Busca todas as empresas do banco
        $roles = Role::whereIn('name', $this->allowedTeamRoles())->get();

        return view('cadastros.usuarios.create', compact('setores', 'empresas', 'roles'));
    }

    /**
     * Armazena um novo usuário no banco de dados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'setor_id' => 'nullable|exists:setores,id', // Setor é opcional (nullable)
            'empresa_id' => 'nullable|exists:empresas,id', // Empresa é opcional (nullable)
            'role' => ['required', Rule::in($this->allowedTeamRoles())],
        ]);

        // Cria o usuário, salvando setor e empresa pelo ID se existir
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'setor_id' => $request->setor_id, // Armazena o ID do setor, null se não for selecionado
            'empresa_id' => $request->empresa_id, // Armazena o ID da empresa, null se não for selecionado
            'status' => true, // Ativo por padrão
        ]);

        // Atribui o papel ao usuário
        $user->assignRole($request->role);

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um usuário existente.
     */
    public function edit(User $user)
    {
        $this->authorizeTeamUserManagement($user);

        $setores = Setor::all(); // Busca todos os setores do banco
        $empresas = Empresa::all(); // Busca todas as empresas do banco
        $roles = Role::whereIn('name', $this->allowedTeamRoles())->get();

        return view('cadastros.usuarios.edit', compact('user', 'setores', 'empresas', 'roles'));
    }

    /**
     * Atualiza um usuário existente.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeTeamUserManagement($user);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'setor_id' => 'nullable|exists:setores,id', // Setor é opcional
            'empresa_id' => 'nullable|exists:empresas,id', // Empresa é opcional
            'role' => ['required', Rule::in($this->allowedTeamRoles())],
            'password' => 'nullable|string|min:8|confirmed' // Valida a senha apenas se preenchida
        ]);

        // Atualiza os dados do usuário
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'setor_id' => $request->setor_id, // Permite que o campo seja nulo
            'empresa_id' => $request->empresa_id, // Permite que o campo seja nulo
        ]);

        // Se o campo de senha for preenchido, atualiza a senha
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Atualiza a permissão (role) do usuário
        $user->syncRoles($request->role);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Desativa um usuário (alterando o status para inativo).
     */

     public function deactivate(User $user)
{
    $this->authorizeTeamUserManagement($user);

    // Verifica se o usuário é o de ID 1 e impede a desativação
    if ($user->id == 1) {
        return redirect()->route('usuarios.index')->with('error', 'Este usuário não pode ser desativado.');
    }

    // Verifica o status atual e inverte o status
    $newStatus = !$user->status;

    // Atualiza o status do usuário
    $user->update(['status' => $newStatus]);

    // Se o status for inativo, deslogar o usuário e limpar o remember_token
    if ($newStatus == 0) {
        // Remove as sessões do usuário
        DB::table('sessions')->where('user_id', $user->id)->delete();

        // Limpa o remember_token para evitar login automático
        $user->forceFill(['remember_token' => null])->save();

        // Verifica se as sessões foram removidas
        $deletedSessions = DB::table('sessions')->where('user_id', $user->id)->count();
        if ($deletedSessions == 0) {
            \Log::info("Usuário {$user->id} deslogado com sucesso.");
        } else {
            \Log::warning("Erro ao deslogar o usuário {$user->id}.");
        }
    }

    // Define a mensagem de acordo com o novo status
    $message = $newStatus ? 'Usuário ativado com sucesso!' : 'Usuário desativado com sucesso!';

    // Redireciona corretamente para a lista de usuários
    return redirect()->route('usuarios.index')->with('success', $message);
}




    /**
     * Exibe o formulário de instalação inicial.
     */
    public function install()
    {
        $roles = Role::all(); // Carrega todas as permissões (roles)
        return view('cadastros.usuarios.install', compact('roles'));
    }

    /**
     * Armazena o primeiro usuário na instalação inicial.
     */
    public function installStore(Request $request)
    {
        // Validação dos campos
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string', // Valida a permissão (role)
        ]);

        // Cria o usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Atribui o papel ao usuário
        $user->assignRole($request->role);

        return redirect('/login')->with('success', 'Usuário criado com sucesso!');
    }




    public function indexClientes()
    {
        // Filtra os usuários que possuem as permissões "cliente" ou "clientedc" e aplica paginação
        $users = User::role(['cliente', 'clientedc'])
                     ->with(['empresa'])
                     ->paginate(10); // Define 10 clientes por página (ajuste conforme necessário)

        // Retorna a view específica para a listagem de clientes
        return view('cadastros.clientes.index', compact('users'));
    }


    public function createCliente(Request $request)
    {
        $empresaId = $request->query('empresa_id'); // Recupera o ID da empresa da query string (ex: ?empresa_id=1)
        $empresa = $empresaId ? Empresa::find($empresaId) : null;

        return view('cadastros.clientes.create', compact('empresa', 'empresaId'));
    }

    public function storeCliente(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'empresa_id' => 'nullable|exists:empresas,id', // Empresa é opcional
        ]);

        // Cria o cliente
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'empresa_id' => $request->empresa_id, // Armazena o ID da empresa, null se não for selecionado
            'status' => true, // Ativo por padrão
        ]);

        // Todo contato criado por este fluxo pertence à Central HelpDesk.
        $user->assignRole('cliente');

        // Define a mensagem de sucesso
        $message = 'Contato criado com sucesso!';

        // Redireciona para a página correta
        if ($request->filled('empresa_id')) {
            // Se o cliente está associado a uma empresa, redireciona para a página de edição da empresa
            return redirect()->route('empresas.edit', $request->empresa_id)->with('success', $message);
        }

        // Caso contrário, redireciona para a lista geral de clientes
        return redirect()->route('clientes.index')->with('success', $message);
    }



public function editCliente(User $user)
{
    $this->authorizeClientManagement($user);

    $empresas = Empresa::all(); // Busca todas as empresas
    $roles = Role::whereIn('name', ['cliente', 'clientedc'])->get(); // Apenas permissões de clientes
    $empresaId = $user->empresa_id; // Pega a empresa associada ao cliente

    // Retorna a view de edição de clientes
    return view('cadastros.clientes.edit', compact('user', 'empresas', 'roles', 'empresaId'));
}

public function updateCliente(Request $request, User $user)
{
    $this->authorizeClientManagement($user);

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'empresa_id' => 'nullable|exists:empresas,id', // Empresa é opcional
        'role' => ['required', Rule::in(['cliente', 'clientedc'])],
        'password' => 'nullable|string|min:8|confirmed' // Valida a senha apenas se preenchida
    ]);

    // Atualiza o cliente
    $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'empresa_id' => $request->empresa_id, // Permite que o campo seja nulo
    ]);

    // Se o campo de senha for preenchido, atualiza a senha
    if ($request->filled('password')) {
        $user->update(['password' => Hash::make($request->password)]);
    }

    // Atualiza a permissão (role) do cliente
    $user->syncRoles($request->role);

    $message = "Contato atualizado com sucesso!";

    // Redireciona para a lista de clientes
    return redirect()->route('empresas.edit', $request->empresa_id)->with('success', $message);
}


public function deactivateCliente(User $user)
{
    $this->authorizeClientManagement($user);

    // Verifica o status atual e inverte o status
    $newStatus = !$user->status;

    // Atualiza o status do cliente
    $user->update(['status' => $newStatus]);

    // Se o cliente foi desativado, remover as sessões ativas e o token `remember_me`
    if ($newStatus == 0) {
        // Remove todas as sessões ativas do cliente
        DB::table('sessions')->where('user_id', $user->id)->delete();

        // Limpa o token `remember_me` para evitar recriação automática da sessão
        $user->update(['remember_token' => null]);
    }

    // Define a mensagem de acordo com o novo status
    $message = $newStatus ? 'Contato ativado com sucesso!' : 'Contato desativado com sucesso!';

    // Redireciona para a página correta
    if ($user->empresa_id) {
        // Se o cliente pertence a uma empresa, redireciona para a página de edição da empresa
        return redirect()->route('empresas.edit', $user->empresa_id)->with('success', $message);
    }

    // Se o cliente não pertence a uma empresa, redireciona para a lista de clientes
    return redirect()->route('clientes.index')->with('success', $message);
}


public function editMinhaConta()
{
    $user = auth()->user(); // Pega o usuário autenticado
    return view('minhaconta.edit', compact('user')); // Retorna a view de edição de conta
}

public function updateMinhaConta(Request $request)
{
    // Validação dos dados
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
    ]);

    // Atualiza o nome e o e-mail
    $user = auth()->user();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->save();

    return redirect()->route('minhaconta.edit')->with('success', 'Perfil atualizado com sucesso!');
}


public function updateMinhaSenha(Request $request)
{
    // Validação das senhas
    $request->validate([
        'current_password' => 'required',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = auth()->user();

    // Verifica se a senha atual está correta
    if (!Hash::check($request->current_password, $user->password)) {
        return redirect()->route('minhaconta.edit')->with('error', 'A senha atual está incorreta.');
    }

    // Atualiza a nova senha
    $user->password = Hash::make($request->password);
    $user->save();

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

private function allowedTeamRoles(): array
{
    return Auth::user()->hasRole('administrador')
        ? ['analista', 'supervisor', 'administrador']
        : ['analista'];
}

private function authorizeTeamUserManagement(User $user): void
{
    abort_unless($user->hasAnyRole(['analista', 'supervisor', 'administrador']), 404);

    if (!Auth::user()->hasRole('administrador')) {
        abort_if(
            $user->hasAnyRole(['supervisor', 'administrador']),
            403,
            'Apenas administradores podem gerenciar supervisores e administradores.'
        );
    }
}

private function authorizeClientManagement(User $user): void
{
    abort_unless($user->hasAnyRole(['cliente', 'clientedc']), 404);
}


}
