<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;

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



