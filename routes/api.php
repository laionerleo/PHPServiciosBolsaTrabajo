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
Route::post('servicio/getDatosEmpresa', [Controller::class, "getDetalleEmpresa"]);
Route::post('servicio/getDatosCandidato', [Controller::class, "getDetalleCandidato"]);
Route::post('servicio/crearcandidato', [ServiciosControler::class, "crearcandidato"]);
Route::post('servicio/crearcandidatoempresa', [ServiciosControler::class, "crearcandidatoempresa"]);
Route::post('servicio/listarempleosbyempresa', [ServiciosControler::class, "listarempleosbyempresa"]);
Route::post('servicio/listarempleosbyempresames', [ServiciosControler::class, "listarempleosbyempresames"]);
Route::post('servicio/listarempleosbycandidato', [ServiciosControler::class, "listarempleosbycandidato"]);
Route::post('servicio/listarempleosbycandidatomes', [ServiciosControler::class, "listarempleosbycandidatomes"]);

Route::get('servicio/listarplantillas', [Controller::class, "getplantillas"]);






// Ruta para el login
Route::post('login', [ServiciosControler::class, 'login']);

// Ruta para obtener las empresas (protegida por JWT)
Route::middleware('jwt.auth')->post('getdatosusuario', [ServiciosControler::class, 'getDatosUsuario']);

//listar  empleos by empresa
Route::middleware('jwt.auth')->post('listarempleosbyempresa', [ServiciosControler::class, 'getDatosUsuario']);


// registro del usuario  ya sea candidato o empresa
Route::middleware('jwt.auth')->post('registrousuario', [ServiciosControler::class, 'getDatosUsuario']);


// aplicar a trabajo 
Route::middleware('jwt.auth')->post('aplicaratrabajo', [ServiciosControler::class, 'getDatosUsuario']);





//listar empleos a los que etsoy postulando 
Route::middleware('jwt.auth')->post('listarempleosbycandidatos', [ServiciosControler::class, 'getDatosUsuario']);

//listar candidatos bya emplos  empleos a los que etsoy postulando 
Route::middleware('jwt.auth')->post('listarcandidatosbyempleos', [ServiciosControler::class, 'getDatosUsuario']);


Route::middleware('jwt.auth')->post('servicio/crearcurriculumcandidato', [ServiciosControler::class, 'crearcurriculumcandidato']);


