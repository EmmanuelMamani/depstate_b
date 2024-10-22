<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\bloqueController;
use App\Http\Controllers\departamentoController;
use App\Http\Controllers\reciboController;
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});
//bloque
Route::post('/bloque', [bloqueController::class, 'create'])->middleware('auth:api');
Route::get('/bloque', [bloqueController::class, 'bloques'])->middleware('auth:api');
Route::get('/bloque/{id}/departamentos', [bloqueController::class, 'departamentos'])->middleware('auth:api');
//recibo
Route::post('/recibo', [reciboController::class, 'create'])->middleware('auth:api');
Route::get('/recibo/{id}/detalles', [reciboController::class, 'detalles'])->middleware('auth:api');
Route::patch('/recibo/{id}/pagar', [reciboController::class, 'pagar'])->middleware('auth:api');
//departamento
Route::get('/departamento/{id}/recibos', [departamentoController::class, 'recibos'])->middleware('auth:api');

