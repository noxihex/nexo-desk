<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SetorController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MensagemController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RelatorioAnalistaController;
use App\Http\Controllers\VisaoGeralController;
use App\Http\Controllers\AdministracaoController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClienteTicketController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\Cadastros\ServicoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redireciona para /home quando acessar a raiz "/"
Route::get('/', function () {
    return redirect('/home');
});




// Rotas de autenticação
Auth::routes();



Route::middleware('auth')->group(function () {
    // Listar notificações
    Route::get('/notificacoes', [NotificacaoController::class, 'index'])->name('notificacoes.index');

    // Marcar notificações como lidas
    Route::post('/notificacoes/marcar-como-lida/{id}', [NotificacaoController::class, 'marcarComoLida'])->name('notificacoes.marcarComoLida');
});

Route::middleware('auth')->prefix('tickets/cliente')->name('tickets.cliente.')->group(function () {
    // Formulário para criar um novo ticket
    Route::get('/create', [ClienteTicketController::class, 'create'])->name('create');
    // Rota para armazenar o ticket criado
    Route::post('/store', [ClienteTicketController::class, 'store'])->name('store');
    // Calcular horas sugeridas
    Route::get('/{id}/horas-sugeridas', [ClienteTicketController::class, 'calcularHorasSugeridas'])->name('calcularHorasSugeridas');
});

Route::middleware('auth')->prefix('tickets/cliente')->name('tickets.cliente.')->group(function () {
    Route::put('/{id}/finalize', [ClienteTicketController::class, 'finalize'])->name('finalize');
});

Route::get('/tickets/pendentes', [TicketController::class, 'pendentes'])
    ->name('tickets.pendentes')
    ->middleware('auth', 'verifica.status');

Route::get('/tickets/my', [TicketController::class, 'myTickets'])->name('tickets.my')->middleware('auth', 'verifica.status');

// Grupo de rotas protegidas por autenticação (somente usuários logados)
Route::middleware('auth', 'verifica.status')->group(function () {


    Route::get('/home', [VisaoGeralController::class, 'index'])->name('home');
    Route::get('/', [App\Http\Controllers\VisaoGeralController::class, 'index']); // Remove o ->name('home')
    Route::get('/tickets/atencao', [VisaoGeralController::class, 'obterTicketsAtencao'])->name('tickets.atencao');

    Route::get('administracao/usuarioslogados', [AdministracaoController::class, 'usuariosLogados'])->name('administracao.usuarioslogados');
    Route::delete('administracao/usuarioslogados/{sessionId}', [AdministracaoController::class, 'deslogarUsuario'])->name('administracao.usuarioslogados.deslogar');

    Route::get('/administracao/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::get('/administracao/backup/download/{id}', [BackupController::class, 'download'])->name('backup.download');


    Route::get('/administracao/auditoria', [AuditController::class, 'index'])->name('auditoria.index');

    Route::get('/tickets/{id}/horas-sugeridas', [TicketController::class, 'calcularHorasSugeridas'])->name('tickets.horasSugeridas');

    Route::get('/tickets/cliente', [ClienteTicketController::class, 'index'])->name('tickets.cliente.index');

    Route::get('/tickets/cliente/{id}', [ClienteTicketController::class, 'show'])->name('tickets.cliente.show');

    Route::post('/tickets/cliente/{id}/mensagens', [ClienteTicketController::class, 'storeMessage'])->name('tickets.cliente.mensagens.store');

    Route::get('/servicos/{servico}/questionario', [ClienteTicketController::class, 'getQuestionario'])->name('servicos.questionario');


// Rota para carregar a página/modal de transferência (GET)
Route::get('/tickets/{ticket}/transferir', [TicketController::class, 'carregarTransferir'])
    ->name('tickets.carregarTransferir'); // Nome ajustado para evitar conflitos

// Rota para realizar a transferência (POST)
Route::post('/tickets/{ticket}/transferir', [TicketController::class, 'transferirTicket'])
    ->name('tickets.transferir'); // Nome para a ação POST


// Rota para exibir o modal ou página para assumir o ticket (GET)
Route::get('/tickets/{ticket}/assumir', [TicketController::class, 'carregarAssumir'])->name('tickets.assumir.view');

// Rota para processar a ação de assumir o ticket (POST)
Route::post('/tickets/{ticket}/assumir', [TicketController::class, 'assumirTicket'])->name('tickets.assumir');


             // Rotas para gestão de Tickets
    Route::resource('tickets', TicketController::class);



    Route::get('/setores/{setorId}/categorias', [CategoriaController::class, 'categoriasPorSetor']);
    Route::get('/categorias/{setor_id}', [TicketController::class, 'carregarCategorias'])
    ->name('categorias.porSetor');

    Route::get('/relatorios/horas', [RelatorioController::class, 'index'])->name('relatorios.horas');
    Route::get('relatorios/analista', [RelatorioAnalistaController::class, 'index'])->name('relatorios.analista');

Route::put('/tickets/{id}/finalize', [TicketController::class, 'finalize'])->name('tickets.finalize');

Route::post('/tickets/{ticket}/mensagens', [MensagemController::class, 'store'])->name('mensagens.store');




 // Rota para download de anexos específicos do ticket
 Route::get('/tickets/{ticket}/attachments/{attachment}', [TicketController::class, 'downloadAttachment'])
 ->name('tickets.downloadAttachment');

            Route::get('/clientes/{id}/empresa', [UserController::class, 'getEmpresa'])->name('clientes.empresa');


            // Rotas para painel MinhaConta
            Route::get('/minhaconta', [UserController::class, 'editMinhaConta'])->name('minhaconta.edit');  // Exibe o formulário de edição da conta
            Route::put('/minhaconta', [UserController::class, 'updateMinhaConta'])->name('minhaconta.update');  // Atualiza nome e e-mail
            Route::put('/minhaconta/password', [UserController::class, 'updateMinhaSenha'])->name('minhaconta.password');  // Atualiza a senha

    // Prefixa todas as rotas de cadastros com "cadastros"
    Route::prefix('cadastros')->group(function () {


        // Rotas para gestão de contratos
        Route::resource('contratos', ContratoController::class);

        // Rotas para gestão de categorias
        Route::resource('categorias', CategoriaController::class);

        // Rotas para gestão de grupos
        Route::resource('grupos', GrupoController::class);

        // Rotas para gestão de empresas
        Route::resource('empresas', EmpresaController::class);


     // Rotas para gestão de clientes (usando as funções específicas para clientes no UserController)
     Route::get('/clientes', [UserController::class, 'indexClientes'])->name('clientes.index');
     Route::get('/clientes/create', [UserController::class, 'createCliente'])->name('clientes.create');  // Usando função createCliente
     Route::post('/clientes', [UserController::class, 'storeCliente'])->name('clientes.store');  // Usando função storeCliente
     Route::get('/clientes/{user}/edit', [UserController::class, 'editCliente'])->name('clientes.edit');  // Usando função editCliente
     Route::put('/clientes/{user}', [UserController::class, 'updateCliente'])->name('clientes.update');  // Usando função updateCliente
     Route::post('/clientes/{user}/deactivate', [UserController::class, 'deactivateCliente'])->name('clientes.deactivate');  // Ativar/Desativar cliente


        // Rotas para gestão de usuários
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{user}/deactivate', [UserController::class, 'deactivate'])->name('usuarios.deactivate');

        // Rotas para gestão de setores
        Route::resource('setores', SetorController::class)->parameters([
            'setores' => 'setor',
        ]);

        Route::get('/empresas/{empresa}/servicos/create', [ServicoController::class, 'create'])->name('servicos.create');
        Route::post('/empresas/{empresa}/servicos', [ServicoController::class, 'store'])->name('servicos.store');
        Route::get('/servicos/{servico}/edit', [ServicoController::class, 'edit'])->name('servicos.edit');
        Route::put('/servicos/{servico}', [ServicoController::class, 'update'])->name('servicos.update');
        Route::delete('/servicos/{servico}', [ServicoController::class, 'destroy'])->name('servicos.destroy');
            



    
    
    });
    
});

