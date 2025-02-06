<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

use App\Http\Controllers\ServiciosControler;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('servicio/listarempleos', [Controller::class, "getlistaempleos"]);
Route::post('servicio/listarempresas', [Controller::class, "getlistarempresas"]);
Route::get('servicio/listarcategorias', [Controller::class, "getListaCategorias"]);
Route::post('servicio/getDatosEmpleo', [Controller::class, "getDetalleEmpleo"]);


// Ruta para el login
Route::post('login', [ServiciosControler::class, 'login']);

// Ruta para obtener las empresas (protegida por JWT)
Route::middleware('jwt.auth')->get('companies', [ServiciosControler::class, 'getCompanies']);