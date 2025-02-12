<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB; // Usamos DB para consultas directas
use Illuminate\Support\Facades\Hash;
use App\Models\usuario;


class ServiciosControler extends Controller
{
    //
       // Método para login
       // Método para login (sin usar modelos)
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $loUsuario = usuario::where('Correo', $credentials['email'])->first();

        
        // Hacemos una consulta directa a la tabla `companies`
        //$loUsuario = DB::table('usuario')->where('Correo', $credentials['email'])->first();

        // Verificar si se encontró la empresa y la contraseña es correcta
        if (!$loUsuario || !Hash::check($credentials['password'], $loUsuario->Contraseña)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Generar el token JWT para la empresa (sin usar modelo)
        $token = JWTAuth::fromUser($loUsuario);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(compact('token'));
    }


          // Método para validar token y traer los datos
    public function getDatosUsuario(Request $request)
    {
        try {
            // Validar el token
            $loUsuario = JWTAuth::parseToken()->authenticate();
            $lnUsuario =$loUsuario->Usuario;

            //traer datoc ocom dcadidato
            $loCandidato = DB::select("SELECT * 
                                        FROM candidato
                                        WHERE Usuario=$lnUsuario");

            //traer datos de empresa si en caso tiene 

            $loEmpresa = DB::select("SELECT * 
                                        FROM usuarioempresa ue, empresa e
                                        WHERE ue.Empresa=e.Empresa
                                        and ue.Usuario =$lnUsuario ");



            return response()->json([
                'message' => 'Datos obtenidos.',
                'error' => false,
                'Datos' => [
                            "Usuario"=>$loUsuario,
                            "Candidato"=>$loCandidato[0],
                            "Empresa"=>@$loEmpresa[0]
                            ]
            ]);
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'No valid token found.'."ERROR". $e->getMessage(),
                'error' => true
            ], 401);
        }
    }


    //------------
    // Método para validar token y traer los datos
    public function registrousuario(Request $request)
    {
        try {
            // Validar el token
            $loUsuario = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'message' => 'Token is valid.',
                'error' => false,
                'Usuario' => $loUsuario
            ]);
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'No valid token found.',
                'error' => true
            ], 401);
        }
    }


    // Método para validar token y traer los datos
    public function aplicaratrabajo(Request $request)
    {
        try {
            // Validar el token
            $loUsuario = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'message' => 'Token is valid.',
                'error' => false,
                'Usuario' => $loUsuario
            ]);
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'No valid token found.',
                'error' => true
            ], 401);
        }
    }





                      // Método para validar token y traer los datos
    public function listarempleosbyempresa(Request $request)
    {
        try {
            // Validar el token
            $loUsuario = JWTAuth::parseToken()->authenticate();
            $lnUsuario =$loUsuario->Usuario;
            $loEmpresa = DB::select("SELECT * 
                                    FROM usuarioempresa ue, empresa e
                                    WHERE ue.Empresa=e.Empresa
                                    and ue.Usuario =$lnUsuario ");
            $lnEmpresa=$loEmpresa[0]->Empresa;

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
            ->where('e.Empresa', '=', $lnEmpresa) // Añadir condición WHERE
            ->get();
            


            return response()->json([
                'message' => 'Token is valid.',
                'error' => false,
                'Usuario' => $loUsuario
            ]);
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'No valid token found.',
                'error' => true
            ], 401);
        }
    }



    public function listarempleosbycandidato(Request $request)
    {
        try {
            // Validar el token
            $loUsuario = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'message' => 'Token is valid.',
                'error' => false,
                'Usuario' => $loUsuario
            ]);
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'No valid token found.',
                'error' => true
            ], 401);
        }
    }

    



}
