<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Utils\mPaqueteEmpleo; // Asegúrate de que la ruta sea correcta

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // Add:19/06/2025, By:Yony Zarate Paco, Nota: Metodo para obtener los datos basicos de la aplicacion
    // Este metodo obtiene los datos basicos de la aplicacion, como paises, ciudades
    public function ObtenerDatosBasicos(Request $request)
    {
        $loPaquete = new mPaqueteEmpleo(1, 0, "Error ...", null);
        try {
            $lcListarPais = "SELECT P.* FROM pais AS P where P.Estado = 1";
            $loDatosPais = DB::select($lcListarPais);

            $lcListarCiudad = "SELECT C.* FROM ciudad AS C where C.Estado = 1";
            $loDatosCiudad = DB::select($lcListarCiudad);

            $lcListaremple = "SELECT * FROM empleo  ";
            $loDatosEmpleo = DB::select($lcListaremple);

            $lcListaremplesDestacados = "SELECT
                                    e.Empleo,
                                    e.Titulo,
                                    e.Descripcion,
                                    e.FechaVencimiento,
                                    e.SalarioAproximado,
                                    e.FechaPublicacion,
                                    e.Ubicacion,
                                    ci.Nombre AS NombreCiudad,
                                    e.Lat,
                                    e.Lng,
                                    e.Categoria,
                                    e.TiempoExperiencia,
                                    c.Nombre AS CategoriaNombre,
                                    e.Empresa,
                                    emp.EmpresaCodigo,
                                    emp.Nombre AS EmpresaNombre,
                                    emp.Descripcion AS EmpresaDescripcion,
                                    te.Nombre AS TipoEmpleoNombre,
                                    tec.Titulo AS TiempoExperienciaTitulo,
                                    e.CodigoEmpleo
                                FROM empleo AS e
                                LEFT JOIN categoria AS c ON e.Categoria = c.Categoria
                                LEFT JOIN empresa AS emp ON e.Empresa = emp.Empresa
                                LEFT JOIN tipoempleo AS te ON e.TipoEmpleo = te.TipoEmpleo
                                LEFT JOIN ciudad AS ci ON e.Ubicacion= ci.Ciudad
                                LEFT JOIN tiempoexperiencia AS tec ON e.TiempoExperiencia = tec.TiempoExperiencia
                                WHERE e.Estado = 1 AND e.Pagado=1";
            $loDatosEmpleoDestacados = DB::select($lcListaremplesDestacados);

            $loPaquete->error = 0;
            $loPaquete->status = 1;
            $loPaquete->message = "Datos Obtenidos.";
            $loPaquete->values = [
                "DatosDePais" => $loDatosPais,
                "DatosDeCiudad" => $loDatosCiudad,
                "DatosDeEmpleo" => $loDatosEmpleo,
                "DatosDeEmpleoDestacados" => $loDatosEmpleoDestacados,
            ];
            return response()->json($loPaquete);
        } catch (\Throwable $th) {
            $lcMessageError = "Error: " . $th->getMessage() . " \nLinea: " . $th->getLine() . " \nArchivo: " . $th->getFile();

            $loPaquete->message = "Error -: " . $th->getMessage()." \nLinea: " . $th->getLine() . " \nArchivo: " . $th->getFile();

            return response()->json($loPaquete);
        }
    }

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
                ->leftJoin('ciudad as ci', 'e.Ubicacion', '=', 'ci.Ciudad')
                ->leftJoin('tiempoexperiencia as tec', 'e.TiempoExperiencia', '=', 'tec.TiempoExperiencia')
                ->select(
                    'e.Empleo', 'e.Titulo',   'e.DescripcionLarga', 'e.Descripcion', 'e.FechaVencimiento', 'e.SalarioAproximado',
                    'e.FechaPublicacion', 'e.Ubicacion', 'e.Lat', 'e.Lng',
                    'e.Categoria', 'e.TiempoExperiencia',
                    'c.Nombre as CategoriaNombre',
                    'ci.Nombre as NombreCiudad',
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
        $loPaquete = new mPaqueteEmpleo(1, 0, "Error ...", null);
        $tcCodigoEmpresa = $request->input('tcCodigoEmpresa');
        try {
            // Realizar la consulta con las relaciones
            $loEmpresa = DB::table('empresa as e')
                            ->join('tipoempresa as te', 'e.TipoEmpresa', '=', 'te.TipoEmpresa')
                            ->join('tamañoempresa as tme', 'e.TamañoEmpresa', '=', 'tme.TamañoEmpresa')
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
            $loPaquete->error = 0;
            $loPaquete->status = 1;
            $loPaquete->message = "Empresa  obtenidos con éxito.";
            $loPaquete->values = $laDatosEmpresa;

        }
        catch (\Throwable $ex) {
            // Manejo de excepciones
            // $oPaquete = [
            //     'error' => false,
            //     'message' => $ex->getMessage(),
            //     'values' => null
            // ];
            $loPaquete->error = 1;
            $loPaquete->status = 0;
            $loPaquete->message = $ex->getMessage();
            $loPaquete->values = [];
        }

        // Retornar la respuesta en formato JSON
        return response()->json($loPaquete);
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
    public function getAllEmplos( Request $toRequest)
    {


        $loPaquete = new mPaqueteEmpleo(1, 0, "Error ...", null);
        $lnCiudad=$toRequest->tnCiudad ?? 0;
        $lnCategoria=$toRequest->tnCategoria ?? 0;

        try {
            $lcListarEmpleos = "
                                SELECT
                                    e.Empleo,
                                    e.Titulo,
                                    e.Descripcion,
                                    e.FechaVencimiento,
                                    e.SalarioAproximado,
                                    e.FechaPublicacion,
                                    e.Ubicacion,
                                    ci.Nombre AS NombreCiudad,
                                    e.Lat,
                                    e.Lng,
                                    e.Categoria,
                                    e.TiempoExperiencia,
                                    c.Nombre AS CategoriaNombre,
                                    e.Empresa,
                                    emp.EmpresaCodigo,
                                    emp.Nombre AS EmpresaNombre,
                                    emp.Descripcion AS EmpresaDescripcion,
                                    te.Nombre AS TipoEmpleoNombre,
                                    tec.Titulo AS TiempoExperienciaTitulo,
                                    e.CodigoEmpleo
                                FROM empleo AS e
                                LEFT JOIN categoria AS c ON e.Categoria = c.Categoria
                                LEFT JOIN empresa AS emp ON e.Empresa = emp.Empresa
                                LEFT JOIN tipoempleo AS te ON e.TipoEmpleo = te.TipoEmpleo
                                LEFT JOIN ciudad AS ci ON e.Ubicacion= ci.Ciudad
                                LEFT JOIN tiempoexperiencia AS tec ON e.TiempoExperiencia = tec.TiempoExperiencia
                                WHERE e.Estado = 1
                                ";


                                if ($lnCiudad != 0) {
                                    $lcListarEmpleos .= " AND e.Ubicacion = $lnCiudad";
                                }

                                if ($lnCategoria != 0) {
                                    $lcListarEmpleos .= " AND e.Categoria = $lnCategoria";
                                }

            // $lcListarEmpleos .= " 	GROUP BY e.Empleo  ORDER BY e.Empleo DESC";

            $loSqlempleos = DB::select($lcListarEmpleos);



            if (empty($loSqlempleos)) {

                $loPaquete->error = 1;
                $loPaquete->status = 0;
                $loPaquete->message = "No hay lista de empleos.";
                $loPaquete->values = [];

                return response()->json($loPaquete);
            }

            $loPaquete->error = 0;
            $loPaquete->status = 1;
            $loPaquete->message = "Lista de empleos obtenida correctamente.";
            $loPaquete->values = $loSqlempleos;

            return response()->json($loPaquete);
        } catch (\Throwable $th) {

            $lcMessageError = "Error: " . $th->getMessage() . " \nLinea: " . $th->getLine() . " \nArchivo: " . $th->getFile();

            $loPaquete->message = "Error - No se Logro obtener la lista: " . $lcMessageError;

            return response()->json($loPaquete);
        }

    }

}
