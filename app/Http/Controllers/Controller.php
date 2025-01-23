<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use DB;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getlistaempleos(Request $request)
    {
        try {
            // Realizar la consulta con las relaciones
            $empleos = DB::table('empleos as e')
                ->leftJoin('categoria as c', 'e.Categoria', '=', 'c.Categoria')
                ->leftJoin('empresa as emp', 'e.Empresa', '=', 'emp.Empresa')
                ->leftJoin('tipoempleo as te', 'e.TipoEmpleo', '=', 'te.TipoEmpleo')
                ->leftJoin('tiempoexperiencia as tec', 'e.TiempoExperiencia', '=', 'tec.TiempoExperiencia')
                ->select(
                    'e.Empleo', 'e.Titulo', 'e.Descripcion', 'e.FechaVencimiento', 'e.SalarioAproximado', 
                    'e.FechaPublicacion', 'e.Ubicacion', 'e.Lat', 'e.Lng', 
                    'e.Categoria', 'e.TiempoExperiencia', 
                    'c.Nombre as CategoriaNombre', 
                    'emp.Nombre as EmpresaNombre', 'emp.Descripcion as EmpresaDescripcion',
                    'te.Nombre as TipoEmpleoNombre', 'tec.Titulo as TiempoExperienciaTitulo'
                )
                ->get();
    
            // Armar la respuesta
            $oPaquete = [
                'error' => true,
                'message' => 'Empleos obtenidos con éxito.',
                'values' => $empleos
            ];
        }
        catch (\Throwable $ex) {
            // Manejo de excepciones
            $oPaquete = [
                'error' => false,
                'message' => $ex->getMessage(),
                'values' => null
            ];
        }
    
        // Retornar la respuesta en formato JSON
        return response()->json($oPaquete);
    }

    public function getListaCategorias(Request $request)
{
    try {
        // Realizar la consulta para obtener todas las categorías
        $categorias = DB::table('categoria')->select('Categoria', 'Nombre')->get();

        // Armar la respuesta
        $oPaquete = [
            'error' => true,
            'message' => 'Categorías obtenidas con éxito.',
            'values' => $categorias
        ];
    }
    catch (\Throwable $ex) {
        // Manejo de excepciones
        $oPaquete = [
            'error' => false,
            'message' => $ex->getMessage(),
            'values' => null
        ];
    }

    // Retornar la respuesta en formato JSON
    return response()->json($oPaquete);
}

    

}
