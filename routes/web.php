<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
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
use App\Http\Controllers\InboundMailboxController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\NotificationPreferenceController;

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
Auth::routes(['register' => false]);



// --- ROTAS DO CLIENTE ---
Route::middleware(['auth', 'verifica.status', 'role:cliente'])->prefix('tickets/cliente')->name('tickets.cliente.')->group(function () {
    // Listagem
    Route::get('/', [ClienteTicketController::class, 'index'])->name('index');
    
    // Criação (Deve vir antes do {id})
    Route::get('/create', [ClienteTicketController::class, 'create'])->name('create');
    Route::post('/store', [ClienteTicketController::class, 'store'])->name('store');
    
    // Exibição e Interação
    Route::get('/{id}', [ClienteTicketController::class, 'show'])->name('show');
    Route::post('/{id}/mensagens', [ClienteTicketController::class, 'storeMessage'])->name('mensagens.store');
    Route::put('/{id}/finalize', [ClienteTicketController::class, 'finalize'])->name('finalize');
    Route::get('/{id}/horas-sugeridas', [ClienteTicketController::class, 'calcularHorasSugeridas'])->name('calcularHorasSugeridas');
});

// --- ROTAS DO STAFF (Analistas, Supervisores e Administradores) ---
Route::middleware(['auth', 'verifica.status', 'role:analista|supervisor|administrador'])->group(function () {

    Route::get('/tickets/atencao', [VisaoGeralController::class, 'obterTicketsAtencao'])->name('tickets.atencao');
    
    Route::get('/tickets/pendentes', [TicketController::class, 'pendentes'])->name('tickets.pendentes');
    Route::get('/tickets/my', [TicketController::class, 'myTickets'])->name('tickets.my');

    // Operação básica de Tickets
    Route::resource('tickets', TicketController::class);
    
    Route::put('/tickets/{id}/finalize', [TicketController::class, 'finalize'])->name('tickets.finalize');
    Route::get('/tickets/{id}/horas-sugeridas', [TicketController::class, 'calcularHorasSugeridas'])->name('tickets.horasSugeridas');
    Route::post('/tickets/{ticket}/mensagens', [MensagemController::class, 'store'])->name('mensagens.store');

    // Transferência e Assumir
    Route::get('/tickets/{ticket}/transferir', [TicketController::class, 'carregarTransferir'])->name('tickets.carregarTransferir');
    Route::post('/tickets/{ticket}/transferir', [TicketController::class, 'transferirTicket'])->name('tickets.transferir');
    Route::get('/tickets/{ticket}/assumir', [TicketController::class, 'carregarAssumir'])->name('tickets.assumir.view');
    Route::post('/tickets/{ticket}/assumir', [TicketController::class, 'assumirTicket'])->name('tickets.assumir');
    Route::get('/tickets/{ticket}/attachments/{attachment}', [TicketController::class, 'downloadAttachment'])->name('tickets.downloadAttachment');

    // Utilitários de carregamento dinâmico
    Route::get('/setores/{setorId}/categorias', [CategoriaController::class, 'categoriasPorSetor']);
    Route::get('/categorias/{setor_id}', [TicketController::class, 'carregarCategorias'])->name('categorias.porSetor');

    // Usado nos formulários de tickets do staff para preencher a empresa do contato.
    // Não deve ficar disponível para clientes, pois permite consultar dados por ID.
    Route::get('/clientes/{id}/empresa', [UserController::class, 'getEmpresa'])->name('clientes.empresa');
});

// --- ROTAS DE GESTÃO (Apenas Supervisores e Administradores) ---
Route::middleware(['auth', 'verifica.status', 'role:supervisor|administrador'])->group(function () {
    
    // Relatórios
    Route::get('/relatorios/horas', [RelatorioController::class, 'index'])->name('relatorios.horas');
    Route::get('relatorios/analista', [RelatorioAnalistaController::class, 'index'])->name('relatorios.analista');

    // Prefixa todas as rotas de cadastros
    Route::prefix('cadastros')->group(function () {
        Route::resource('categorias', CategoriaController::class);
        Route::resource('empresas', EmpresaController::class);
        Route::resource('setores', SetorController::class)->parameters(['setores' => 'setor']);

        // Gestão de clientes e usuários (AQUI O ANALISTA SERÁ BARRADO VIA URL)
        Route::get('/clientes', [UserController::class, 'indexClientes'])->name('clientes.index');
        Route::get('/clientes/create', [UserController::class, 'createCliente'])->name('clientes.create');
        Route::post('/clientes', [UserController::class, 'storeCliente'])->name('clientes.store');
        Route::get('/clientes/{user}/edit', [UserController::class, 'editCliente'])->name('clientes.edit');
        Route::put('/clientes/{user}', [UserController::class, 'updateCliente'])->name('clientes.update');
        Route::post('/clientes/{user}/deactivate', [UserController::class, 'deactivateCliente'])->name('clientes.deactivate');

        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{user}/deactivate', [UserController::class, 'deactivate'])->name('usuarios.deactivate');

    });
});

// --- ROTAS DE ADMINISTRAÇÃO (Apenas Administradores) ---
Route::middleware(['auth', 'verifica.status', 'role:administrador'])->group(function () {
    Route::get('administracao/usuarioslogados', [AdministracaoController::class, 'usuariosLogados'])->name('administracao.usuarioslogados');
    Route::delete('administracao/usuarioslogados/{sessionId}', [AdministracaoController::class, 'deslogarUsuario'])->name('administracao.usuarioslogados.deslogar');
    Route::get('/administracao/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::get('/administracao/backup/download/{id}', [BackupController::class, 'download'])->name('backup.download');
    Route::get('/administracao/auditoria', [AuditController::class, 'index'])->name('auditoria.index');
    Route::resource('/administracao/caixas-email', InboundMailboxController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['caixas-email' => 'mailbox'])
        ->names('inbound-mailboxes');
});

// --- ROTAS COMPARTILHADAS (Qualquer usuário logado) ---
Route::middleware(['auth', 'verifica.status'])->group(function () {
    Route::get('/home', [VisaoGeralController::class, 'index'])->name('home');
    Route::get('/minhaconta', [UserController::class, 'editMinhaConta'])->name('minhaconta.edit');
    Route::put('/minhaconta', [UserController::class, 'updateMinhaConta'])->name('minhaconta.update');
    Route::put('/minhaconta/password', [UserController::class, 'updateMinhaSenha'])->name('minhaconta.password');
    Route::get('/notificacoes', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::post('/notificacoes/{id}/lida', [NotificationCenterController::class, 'read'])->name('notifications.read');
    Route::post('/notificacoes/lidas', [NotificationCenterController::class, 'readAll'])->name('notifications.read-all');
    Route::put('/minhaconta/notificacoes', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');
});
