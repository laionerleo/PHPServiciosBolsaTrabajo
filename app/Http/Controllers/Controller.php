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
            $empleos = DB::table('empleo as e')
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
                    'te.Nombre as TipoEmpleoNombre', 'tec.Titulo as TiempoExperienciaTitulo',
                    'e.CodigoEmpleo'

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


    public function getDetalleEmpleo(Request $request)
    {

        $tcCodigoEmpleo = $request->input('tcCodigoEmpleo');
        try {
            // Realizar la consulta con las relaciones
            $empleos = DB::table('empleo as e')
                ->leftJoin('categoria as c', 'e.Categoria', '=', 'c.Categoria')
                ->leftJoin('empresa as emp', 'e.Empresa', '=', 'emp.Empresa')
                ->leftJoin('tipoempleo as te', 'e.TipoEmpleo', '=', 'te.TipoEmpleo')
                ->leftJoin('tiempoexperiencia as tec', 'e.TiempoExperiencia', '=', 'tec.TiempoExperiencia')
                ->select(
                    'e.Empleo', 'e.Titulo',   'e.DescripcionLarga', 'e.Descripcion', 'e.FechaVencimiento', 'e.SalarioAproximado', 
                    'e.FechaPublicacion', 'e.Ubicacion', 'e.Lat', 'e.Lng', 
                    'e.Categoria', 'e.TiempoExperiencia', 
                    'c.Nombre as CategoriaNombre', 
                    'emp.Nombre as EmpresaNombre', 'emp.Descripcion as EmpresaDescripcion',
                    'te.Nombre as TipoEmpleoNombre', 'tec.Titulo as TiempoExperienciaTitulo'
                )
                ->where("e.CodigoEmpleo", $tcCodigoEmpleo)

                ->get();

                // traer requerimiento 
                $empleosrequerimiento = DB::table('empleo as e')
                ->leftJoin('empleorequerimiento as er', 'e.Empleo', '=', 'er.Empleo')
                ->select(
                    'e.Empleo', 'er.Serial', 'er.Descripcion'
                )
                ->where("e.CodigoEmpleo", $tcCodigoEmpleo)
                ->get();

                //traer habilidades
                $empleoresponsabilidades = DB::table('empleo as e')
                ->leftJoin('empleoresponsabilidades as er', 'e.Empleo', '=', 'er.Empleo')
                ->select(
                    'e.Empleo', 'er.Serial', 'er.Descripcion'
                )
                ->where("e.CodigoEmpleo", $tcCodigoEmpleo)
                ->get();

                $empleohabilidades = DB::table('empleo as e')
                ->leftJoin('empleohabilidades as er', 'e.Empleo', '=', 'er.Empleos')
                ->leftJoin('habilidades as hab', 'er.Habilidades', '=', 'hab.Habilidades')
                ->select(
                    'e.Empleo', 'er.Serial', 'hab.Descripcion'
                )
                ->where("e.CodigoEmpleo", $tcCodigoEmpleo)
                ->get();

            $laDatosEmpleo=[
                "loEmpleo"=> $empleos[0],
                "laEmpleoRequerimiento"=>$empleosrequerimiento ,
                "laEmpleoResponsabilidades"=> $empleoresponsabilidades,
                "laEmpleoHabilidades"=> $empleohabilidades
            ] ;
            // Armar la respuesta
            $oPaquete = [
                'error' => true,
                'message' => 'Empleos obtenidos con éxito.',
                'values' => $laDatosEmpleo
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

    
    public function getDetalleEmpresa(Request $request)
    {

        $tcCodigoEmpresa = $request->input('tcCodigoEmpresa');
        try {
            // Realizar la consulta con las relaciones
            $loEmpresa = DB::table('Empresa as e')
                            ->join('TipoEmpresa as te', 'e.TipoEmpresa', '=', 'te.TipoEmpresa')
                            ->join('TamañoEmpresa as tme', 'e.TamañoEmpresa', '=', 'tme.TamañoEmpresa')
                            ->select(
                                'e.*',
                                'te.descripcion as TipoEmpresaDescripcion',
                                'tme.descripcion as TamanoEmpresaDescripcion',
                                'e.AñoFundacion as AnioFundacion'
                            )
                            ->where('e.EmpresaCodigo', '=', $tcCodigoEmpresa)
                            ->first(); // Usamos 'first' porque estamos esperando un solo resultado



                // traer requerimiento 
            

            $laDatosEmpresa=[
                "loEmpresa"=> @$loEmpresa,
               // "laEmpleoRequerimiento"=>$empleosrequerimiento ,
                //"laEmpleoResponsabilidades"=> $empleoresponsabilidades,
            ] ;
            // Armar la respuesta
            $oPaquete = [
                'error' => true,
                'message' => 'Empresa  obtenidos con éxito.',
                'values' => $laDatosEmpresa
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


    
    public function getDetalleCandidato(Request $request)
    {

        $tcCodigoCandidato = $request->input('tcCodigoCandidato');
        try {
            // Realizar la consulta con las relaciones
            $loCandidato = DB::table('candidato as e')
                //->leftJoin('categoria as c', 'e.Categoria', '=', 'c.Categoria')
                //->leftJoin('empresa as emp', 'e.Empresa', '=', 'emp.Empresa')
                //->leftJoin('tipoempleo as te', 'e.TipoEmpleo', '=', 'te.TipoEmpleo')
                //->leftJoin('tiempoexperiencia as tec', 'e.TiempoExperiencia', '=', 'tec.TiempoExperiencia')
            /*    ->select(
                    'e.Empleo', 'e.Titulo',   'e.DescripcionLarga', 'e.Descripcion', 'e.FechaVencimiento', 'e.SalarioAproximado', 
                    'e.FechaPublicacion', 'e.Ubicacion', 'e.Lat', 'e.Lng', 
                    'e.Categoria', 'e.TiempoExperiencia', 
                    'c.Nombre as CategoriaNombre', 
                    'emp.Nombre as EmpresaNombre', 'emp.Descripcion as EmpresaDescripcion',
                    'te.Nombre as TipoEmpleoNombre', 'tec.Titulo as TiempoExperienciaTitulo'
                )*/
                ->where("e.CandidatoCodigo", $tcCodigoCandidato)

                ->get();

                /*
                // traer requerimiento 
                $empleosrequerimiento = DB::table('empleo as e')
                ->leftJoin('empleorequerimiento as er', 'e.Empleo', '=', 'er.Empleo')
                ->select(
                    'e.Empleo', 'er.Serial', 'er.Descripcion'
                )
                ->where("e.CodigoEmpleo", $tcCodigoEmpleo)
                ->get();

                //traer habilidades
                $empleoresponsabilidades = DB::table('empleo as e')
                ->leftJoin('empleoresponsabilidades as er', 'e.Empleo', '=', 'er.Empleo')
                ->select(
                    'e.Empleo', 'er.Serial', 'er.Descripcion'
                )
                ->where("e.CodigoEmpleo", $tcCodigoEmpleo)
                ->get();
                */

                /*
            $laDatosEmpleo=[
                "loEmpleo"=> $empleos[0],
                "laEmpleoRequerimiento"=>$empleosrequerimiento ,
                "laEmpleoResponsabilidades"=> $empleoresponsabilidades,
            ] ;
            */
            // Armar la respuesta
            $oPaquete = [
                'error' => true,
                'message' => 'Empleos obtenidos con éxito.',
                'values' => @$loCandidato[0]
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
    

    public function getplantillas(Request $request)
    {
        try {
            // Realizar la consulta para obtener todas las categorías
            $categorias = DB::table('plantillacurriculum')->select('PlantillaCurriculum', 'NombreDescripcion',  'HtmlPlantilla')->get();

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

    public function getPostulantesEmpleo(Request $request)
    {

        $tcCodigoEmpleo = $request->input('tcCodigoEmpleo');
        try {
            // Realizar la consulta con las relaciones
            $codigoEmpleo = 'EMP-2025-FULLSTACK';

            $laPostulantes = DB::table('candidato as c')
                ->join('candidatoempleo as ce', 'ce.Candidato', '=', 'c.Candidato')
                ->join('empleo as e', 'e.Empleo', '=', 'ce.Empleo')
                ->leftJoin('curriculum as cur', 'cur.Candidato', '=', 'c.Candidato')
                ->leftJoin('curriculumhabilidades as ch', 'ch.Curriculum', '=', 'cur.Curriculum')
                ->leftJoin('empleohabilidades as eh', 'eh.Empleos', '=', 'e.Empleo')
                ->select(
                    'c.Candidato',
                    'c.Nombre',
                    'cur.html',
                    //'c.Profesion',
                    'e.Titulo as EmpleoTitulo',
                    'e.TiempoExperiencia as ExperienciaRequerida',
                    'c.AnosExperiencia as ExperienciaCandidato',
                    DB::raw("CASE 
                        WHEN c.AnosExperiencia >= e.TiempoExperiencia THEN 'Cumple'
                        ELSE 'No cumple'
                    END as ComparacionExperiencia"),
                    DB::raw("COUNT(DISTINCT eh.Habilidades) as HabilidadesRequeridas"),
                    DB::raw("COUNT(DISTINCT ch.Habilidades) as HabilidadesCandidato"),
                    DB::raw("CASE            
                        WHEN COUNT(DISTINCT ch.Habilidades) >= COUNT(DISTINCT eh.Habilidades) THEN 'Cumple'
                        ELSE 'No cumple'
                    END as ComparacionHabilidades")
                )
                ->where('e.CodigoEmpleo', '=', $tcCodigoEmpleo)
                ->groupBy(
                    'c.Candidato',
                    'c.Nombre',
                    'e.Titulo',
                    'e.TiempoExperiencia',
                    'c.AnosExperiencia',
                    'cur.html'
                )
                ->get();
            

            // Armar la respuesta
            $oPaquete = [
                'error' => true,
                'message' => 'Empleos obtenidos con éxito.',
                'values' => $laPostulantes
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
