<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\V2\LookupController as V2LookupController;
use App\Http\Controllers\Api\V2\TicketController as V2TicketController;
use App\Http\Controllers\MailgunInboundController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/mailgun/inbound', MailgunInboundController::class)->middleware('throttle:mailgun-inbound');


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/tickets/search', [TicketApiController::class, 'search']);
    Route::get('/tickets', [TicketApiController::class, 'index']);
    Route::get('/tickets/{id}', [TicketApiController::class, 'show']);
    Route::post('/tickets', [TicketApiController::class, 'store']);
    Route::post('/tickets/{id}/finalizar', [TicketApiController::class, 'finalizar']);
    Route::post('/tickets/{id}/messages', [TicketApiController::class, 'addMessage']);
    Route::patch('/tickets/{id}/status', [TicketApiController::class, 'updateStatus']);

    Route::post('/tickets/{id}/assumir', [TicketApiController::class, 'assumirTicket']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v2')->middleware('auth:sanctum')->group(function () {
    Route::get('/tickets/search', [V2TicketController::class, 'search']);
    Route::get('/tickets', [V2TicketController::class, 'index']);
    Route::get('/tickets/{id}', [V2TicketController::class, 'show']);
    Route::post('/tickets', [V2TicketController::class, 'store']);
    Route::post('/tickets/{id}/finalizar', [V2TicketController::class, 'finalizar']);
    Route::post('/tickets/{id}/messages', [V2TicketController::class, 'addMessage']);
    Route::patch('/tickets/{id}/status', [V2TicketController::class, 'updateStatus']);
    Route::post('/tickets/{id}/assumir', [V2TicketController::class, 'assumir']);
    Route::post('/tickets/{id}/transferir', [V2TicketController::class, 'transferir']);

    Route::get('/usuarios', [V2LookupController::class, 'usuarios']);
    Route::get('/empresas', [V2LookupController::class, 'empresas']);
    Route::get('/setores', [V2LookupController::class, 'setores']);
    Route::get('/categorias', [V2LookupController::class, 'categorias']);
    Route::get('/grupos', [V2LookupController::class, 'grupos']);
    Route::get('/me', [V2LookupController::class, 'me']);
});
