<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

class PDFController extends Controller
{
    //
    public function generatePDF()
    {
        // Datos que se enviarán a la vista
        $data = [
            'title' => 'Mi primer PDF en Laravel',
            'date' => date('m/d/Y')
        ];

        // Cargar la vista que se convertirá en PDF
        $pdf = PDF::loadView('myPDF', $data);

        // Descargar el PDF con el nombre 'mi-archivo.pdf'
        return $pdf->download('mi-archivo.pdf');
    }

  

    public function generatePDFCurriculum()
{
    // Datos para la vista
    $data = [
        'title' => 'Curriculum Vitae',
        'date' => date('m/d/Y')
    ];

    // Cargar la vista y generar el PDF
    $pdf = PDF::loadView('myCurriculum2', $data);

    // Descargar el PDF
    return $pdf->download('curriculum-vitae.pdf');
}
}
