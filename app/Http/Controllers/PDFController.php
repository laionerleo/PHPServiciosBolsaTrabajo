<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\DB; // Usamos DB para consultas directas

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
        //return $pdf->download('mi-archivo.pdf');
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


    public function generatePDFCurriculum2($id)
        {
            // Aquí puedes usar $id para buscar datos en base al curriculum
            $tnCurriculum=$id;
            $laCurriculum = DB::select("SELECT * 
                                        FROM curriculum as cu
                                        WHERE cu.Curriculum =$tnCurriculum ");
            $loCurriculum=$laCurriculum[0];
            $pdf = PDF::loadHTML( $loCurriculum->html);

            return $pdf->download("curriculum-$id.pdf");
        }

}
