<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TicketApiController;

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
