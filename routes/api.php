<?php

use App\Http\Controllers\Api\AutenticacionController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\IncidenciaController;
use App\Http\Controllers\Api\OrdenServicioController;
use App\Http\Controllers\Api\RutaController;
use App\Http\Controllers\Api\ServicioProgramadoController;
use App\Http\Controllers\Api\UnidadController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AutenticacionController::class, 'iniciarSesion']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil', [AutenticacionController::class, 'perfil']);
    Route::post('/logout', [AutenticacionController::class, 'cerrarSesion']);

    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'listar']);
        Route::post('/', [UsuarioController::class, 'registrar']);
        Route::get('/{usuario}', [UsuarioController::class, 'mostrar']);
        Route::put('/{usuario}', [UsuarioController::class, 'actualizar']);
        Route::patch('/{usuario}', [UsuarioController::class, 'actualizar']);
        Route::delete('/{usuario}', [UsuarioController::class, 'eliminar']);
    });

    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClienteController::class, 'listar']);
        Route::post('/', [ClienteController::class, 'registrar']);
        Route::get('/{cliente}', [ClienteController::class, 'mostrar']);
        Route::put('/{cliente}', [ClienteController::class, 'actualizar']);
        Route::patch('/{cliente}', [ClienteController::class, 'actualizar']);
        Route::delete('/{cliente}', [ClienteController::class, 'eliminar']);
    });

    Route::prefix('unidades')->group(function () {
        Route::get('/', [UnidadController::class, 'listar']);
        Route::post('/', [UnidadController::class, 'registrar']);
        Route::get('/{unidad}', [UnidadController::class, 'mostrar']);
        Route::put('/{unidad}', [UnidadController::class, 'actualizar']);
        Route::patch('/{unidad}', [UnidadController::class, 'actualizar']);
        Route::delete('/{unidad}', [UnidadController::class, 'eliminar']);
    });

    Route::prefix('ordenes-servicio')->group(function () {
        Route::get('/', [OrdenServicioController::class, 'listar']);
        Route::post('/', [OrdenServicioController::class, 'registrar']);
        Route::get('/{ordenServicio}', [OrdenServicioController::class, 'mostrar']);
        Route::put('/{ordenServicio}', [OrdenServicioController::class, 'actualizar']);
        Route::patch('/{ordenServicio}', [OrdenServicioController::class, 'actualizar']);
        Route::delete('/{ordenServicio}', [OrdenServicioController::class, 'eliminar']);
    });

    Route::prefix('rutas')->group(function () {
        Route::get('/', [RutaController::class, 'listar']);
        Route::post('/', [RutaController::class, 'registrar']);
        Route::get('/{ruta}', [RutaController::class, 'mostrar']);
        Route::put('/{ruta}', [RutaController::class, 'actualizar']);
        Route::patch('/{ruta}', [RutaController::class, 'actualizar']);
        Route::delete('/{ruta}', [RutaController::class, 'eliminar']);
    });

    Route::prefix('incidencias')->group(function () {
        Route::get('/', [IncidenciaController::class, 'listar']);
        Route::post('/', [IncidenciaController::class, 'registrar']);
        Route::get('/{incidencia}', [IncidenciaController::class, 'mostrar']);
        Route::put('/{incidencia}', [IncidenciaController::class, 'actualizar']);
        Route::patch('/{incidencia}', [IncidenciaController::class, 'actualizar']);
        Route::delete('/{incidencia}', [IncidenciaController::class, 'eliminar']);
    });

    Route::prefix('servicios-programados')->group(function () {
        Route::get('/', [ServicioProgramadoController::class, 'listar']);
        Route::post('/', [ServicioProgramadoController::class, 'registrar']);
        Route::get('/{servicioProgramado}', [ServicioProgramadoController::class, 'mostrar']);
        Route::put('/{servicioProgramado}', [ServicioProgramadoController::class, 'actualizar']);
        Route::patch('/{servicioProgramado}', [ServicioProgramadoController::class, 'actualizar']);
        Route::delete('/{servicioProgramado}', [ServicioProgramadoController::class, 'eliminar']);
    });
});
