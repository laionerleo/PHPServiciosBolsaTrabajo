<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericMessageMail; // El mailable que ya hicimos

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

Route::get('/', function () {
    return view('welcome');
});


//Route::get('/generate-pdf', [PDFController::class, 'generatePDF']);

Route::get('/generate-pdf', [PDFController::class, 'generatePDFCurriculum']);
Route::get('/generate-pdf2/{id}', [PDFController::class, 'generatePDFCurriculum2']);




Route::get('/probar-correo', function () {
    $destino = 'leonardo.ayala@pagofacil.com.bo';
    $asunto = 'Hola desde Hostinger';
    $mensaje = 'Este es un mensaje de prueba enviado desde Laravel y Hostinger SMTP.';

    $contenido = ['asunto' => $asunto, 'mensaje' => $mensaje];

    try {
        Mail::to("jo")->send(new GenericMessageMail($asunto, $mensaje));
        return 'Correo enviado con éxito.';
    } catch (\Exception $e) {
        return 'Error al enviar: ' . $e->getMessage();
    }
});

