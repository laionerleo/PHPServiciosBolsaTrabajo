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

    try {
        // Validar si el correo existe en la base de datos
        $loUsuario = usuario::where('Correo', $credentials['email'])->first();

        if (!$loUsuario) {
            // Devolver error si el correo no existe
            return response()->json([
                'message' => 'Error : Correo no encontrado.',
                'error' => true,
                "codigoerror" => 3,
            ]);
        }

        // Validar si la contraseña es correcta
        if (!Hash::check($credentials['password'], $loUsuario->Contraseña)) {
            // Devolver error si la contraseña no coincide
            return response()->json([
                'message' => 'Error  Contraseña incorrecta.',
                'error' => true,
                "codigoerror" => 3,
            ]);
        }

        // Generar el token JWT para el usuario
        $token = JWTAuth::fromUser($loUsuario);

        if (!$token) {
            return response()->json([
                'message' => 'Error al generar el token.',
                'error' => true,
                "codigoerror" => 3,
            ]);
        }

        // Si todo es correcto, devolver el token generado
        return response()->json([
            "codigoerror"=>0,
            'message' => 'Datos obtenidos.',
            'error' => false,
            'datos' => [
                        "token"=>$token
                        ]
        ]);
        ///return response()->json(compact('token'));

    } catch (\Exception $th) {
        return response()->json([
            'message' => 'No valid token found. ERROR: ' . $th->getMessage(),
            'error' => true,
            "codigoerror" => 1,
        ]);
    }
}




          // Método para validar token y traer los datos
    public function getDatosUsuario(Request $request)
    {

        try {
            //code...
            $loUsuario = JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => 'No valid token found.'."ERROR". $th->getMessage(),
                'error' => true,
                "codigoerror"=>1,
            ]);
        }

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
                "codigoerror"=>0,
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
                'error' => true,
                "codigoerror"=>2,
            ]);
        }
    }


    //------------
    // Método para validar token y traer los datos
    public function registrousuario(Request $request)
    {

        try {
            //code...
            $loUsuario = JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => 'No valid token found.'."ERROR". $th->getMessage(),
                'error' => true,
                "codigoerror"=>1,
            ]);
        }
        try {
            // Validar el token
           // $loUsuario = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'message' => 'Token is valid.',
                'error' => false,
                'Usuario' => $loUsuario
            ]);
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'No valid token found.',
                'error' => true,
                "codigoerror"=>2,
            ]);
        }
    }


    // Método para validar token y traer los datos
    public function aplicaratrabajo(Request $request)
    {
        try {
            //code...
            $loUsuario = JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => 'No valid token found.'."ERROR". $th->getMessage(),
                'error' => true,
                "codigoerror"=>1,
            ]);
        }
        
        try {
            // Validar el token
            $loUsuario = JWTAuth::parseToken()->authenticate();
            $lnUsuario =$loUsuario->Usuario;
            $loCandidato = DB::table('candidato')
                            ->where('Usuario', '=', $lnUsuario)
                            ->first();
            $lnCandidato=$loCandidato->Candidato;

            $tcCodigoEmpresa = $request->input('tcCodigoEmpresa');
            $loEmpleo = DB::table('Empleo')
                            ->where('e.CodigoEmpleo', '=', $tcCodigoEmpleo)
                            ->first();
            $lnEmpleo=$loEmpleo->Empleo;
              // Insertar en la tabla usuario


              $ultimoSerial = DB::table('candidatoempleo')
                                ->where('Candidato',  $lnCandidato)
                                ->max('Serial');


            $insercionExitosa = DB::table('candidatoempleo')->insert([ 
                'Candidato' => $lnCandidato,
                'Serial' => $ultimoSerial,
                'Empleo' => $lnEmpleo,
                'Estado' => 1,
                'FechaPostulacion' => now(),
            ]);
            
            if ($insercionExitosa) {
                return response()->json([
                    'message' => 'Inserción correcta',
                    'error' => false,
                    'Datos' => 1,
                ]);
            } else {
                return response()->json([
                    'message' => 'Error en la inserción',
                    'error' => true,
                    'Datos' => 0,
                ]);
            }
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'Error catch'. $e->getMessage(),
                'error' => true,
                "codigoerror"=>2,
            ]);
        }
    }





                      // Método para validar token y traer los datos
    public function listarempleosbyempresa(Request $request)
    {
        try {
            // Validar el token
            
            try {
                //code...
                $loUsuario = JWTAuth::parseToken()->authenticate();
            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'No valid token found.'."ERROR". $th->getMessage(),
                    'error' => true,
                    "codigoerror"=>1,
                ]);
            } 
            $lnUsuario =$loUsuario->Usuario;
            
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
            ->whereIn('e.Empleo', function($query) use ($lnUsuario) {
                $query->select('EE.Empleo')
                      ->from('empresaempleo as EE')
                      ->whereIn('EE.Empresa', function($query) use ($lnUsuario) {
                          $query->select('UE.Empresa')
                                ->from('usuarioempresa as UE')
                                ->where('UE.Usuario', '=', $lnUsuario);
                      });
            })
            ->get();
        
        
            


            return response()->json([
                'message' => 'empleos de la empresa ',
                'error' => false,
                'Datos' => $empleos
            ]);
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'Error catch'. $e->getMessage(),
                'error' => true,
                "codigoerror"=>2,
            ]);
        }
    }



    public function listarempleosbycandidato(Request $request)
    {
        try {
            // Validar el token
            try {
                //code...
                $loUsuario = JWTAuth::parseToken()->authenticate();
            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'No valid token found.'."ERROR". $th->getMessage(),
                    'error' => true,
                    "codigoerror"=>1,
                ]);
            }
            $lnUsuario =$loUsuario->Usuario;
        
            $empleos = DB::table('empleo as e')
            ->leftJoin('categoria as c', 'e.Categoria', '=', 'c.Categoria')
            ->leftJoin('empresa as emp', 'e.Empresa', '=', 'emp.Empresa')
            ->leftJoin('tipoempleo as te', 'e.TipoEmpleo', '=', 'te.TipoEmpleo')
            ->leftJoin('tiempoexperiencia as tec', 'e.TiempoExperiencia', '=', 'tec.TiempoExperiencia')
            ->leftJoin('candidatoempleo as ce', 'e.Empleo', '=', 'ce.Empleo') // Unir con candidatoempleo
            ->leftJoin('candidato as cand', 'ce.Candidato', '=', 'cand.Candidato') // Unir con candidato
            ->select(
                'e.Empleo', 'e.Titulo', 'e.Descripcion', 'e.FechaVencimiento', 'e.SalarioAproximado', 
                'e.FechaPublicacion', 'e.Ubicacion', 'e.Lat', 'e.Lng', 
                'e.Categoria', 'e.TiempoExperiencia', 
                'c.Nombre as CategoriaNombre', 
                'emp.Nombre as EmpresaNombre', 'emp.Descripcion as EmpresaDescripcion',
                'te.Nombre as TipoEmpleoNombre', 'tec.Titulo as TiempoExperienciaTitulo',
                'e.CodigoEmpleo',
                'ce.FechaPostulacion', 'ce.Estado' // Información de la postulación
            )
            ->where('cand.Usuario', '=', $lnUsuario) // Filtrar por el Usuario asociado al candidato
            ->get();
        

            return response()->json([
                'message' => 'empleos de la empresa ',
                'error' => false,
                'Datos' => $empleos
            ]);
        } catch (\Exception $e) {
            // Si no se puede autenticar el token, devolver error
            return response()->json([
                'message' => 'Error catch'. $e->getMessage(),
                'error' => true,
                "codigoerror"=>2,
            ]);
        }
    }

    

    public function crearcandidato(Request $request){
        // Recuperar los datos del request
        $tcCorreo = $request->input('tcCorreo');
        $tcContraseña = $request->input('tcContraseña');
        $tnTelefono = $request->input('tnTelefono');
        $tcNombre = $request->input('tcNombre');
        $tcApellidos = $request->input('tcApellidos');
        $tcProfesion = $request->input('tcProfesion');
    
        // Verificar si el correo ya existe
        $existeUsuario = DB::table('usuario')->where('Correo', $tcCorreo)->exists();
        if ($existeUsuario) {
            // Devolver el paquete de error directamente
            return response()->json([
                'error' => true,
                'message' => 'El correo electrónico ya está registrado.',
                'values' => null
            ]);
        }
    
        try {
            // Iniciar una transacción
            DB::beginTransaction();
    
            // Insertar en la tabla usuario
            $usuarioId = DB::table('usuario')->insertGetId([
                'NombreCompleto' => $tcNombre . ' ' . $tcApellidos,
                'Correo' => $tcCorreo,
                'Telefono' => $tnTelefono,
                'Contraseña' => bcrypt($tcContraseña), // Encriptar la contraseña
                'Estado' => 1,
                'FechaCreacion' => now(),
            ]);
    
            // Insertar en la tabla candidato
            DB::table('candidato')->insert([
                'Nombre' => $tcNombre . ' ' . $tcApellidos,
                'Profesion' => $tcProfesion,
                'Estado' => 1,
                'Usuario' => $usuarioId,
                'CandidatoCodigo' => uniqid(), // Generar un código único para el candidato
                'FechaNacimiento' => null,
                'Acercade' => null,
                'Pais' => null,
                'Ciudad' => null,
                'Sexo' => null,
                'TituloTecnico' => null,
                'TituloLicenciatura' => null,
                'TituloDiplomado' => null,
                'TituloMaestria' => null,
                'TituloDoctorado' => null,
                'AnosExperiencia' => null,
                'Telefono' => $tnTelefono,
            ]);
    
            // Confirmar la transacción (commit)
            DB::commit();
    
            // Armar la respuesta
            $oPaquete = [
                'error' => false,
                'message' => 'Candidato creado con éxito.',
                'values' => [
                    'usuarioId' => $usuarioId,
                ]
            ];
        }
        catch (\Throwable $ex) {
            // Revertir la transacción (rollback) en caso de error
            DB::rollBack();
    
            // Armar la respuesta de error
            $oPaquete = [
                'error' => true,
                'message' => $ex->getMessage(),
                'values' => null,
                "codigoerror"=>2
            ];
           
        }
    
        // Retornar la respuesta en formato JSON
        return response()->json($oPaquete);
    }


    public function crearcandidatoempresa(Request $request){
        // Recuperar los datos del request
        $tcCorreo = $request->input('tcCorreo');
        $tcContraseña = $request->input('tcContraseña');
        $tnTelefono = $request->input('tnTelefono');
        $tcNombre = $request->input('tcNombre');
        $tcApellidos = $request->input('tcApellidos');
        $tcProfesion = $request->input('tcProfesion');

        $tcNombreEmpresa = $request->input('tcNombreEmpresa');
        $tcDescripcion = $request->input('tcDescripcion');
        $tnAnoFundacion = $request->input('tnAnoFundacion');
    
        // Verificar si el correo ya existe
        $existeUsuario = DB::table('usuario')->where('Correo', $tcCorreo)->exists();
        if ($existeUsuario) {
            // Devolver el paquete de error directamente
            return response()->json([
                'error' => true,
                'message' => 'El correo electrónico ya está registrado.',
                'values' => null
            ]);
        }
    
        try {
            // Iniciar una transacción
            DB::beginTransaction();
    
            // Insertar en la tabla usuario
            $usuarioId = DB::table('usuario')->insertGetId([
                'NombreCompleto' => $tcNombre . ' ' . $tcApellidos,
                'Correo' => $tcCorreo,
                'Telefono' => $tnTelefono,
                'Contraseña' => bcrypt($tcContraseña), // Encriptar la contraseña
                'Estado' => 1,
                'FechaCreacion' => now(),
            ]);
    
            // Insertar en la tabla candidato
            DB::table('candidato')->insert([
                'Nombre' => $tcNombre . ' ' . $tcApellidos,
                'Profesion' => $tcProfesion,
                'Estado' => 1,
                'Usuario' => $usuarioId,
                'CandidatoCodigo' => uniqid(), // Generar un código único para el candidato
                'FechaNacimiento' => null,
                'Acercade' => null,
                'Pais' => null,
                'Ciudad' => null,
                'Sexo' => null,
                'TituloTecnico' => null,
                'TituloLicenciatura' => null,
                'TituloDiplomado' => null,
                'TituloMaestria' => null,
                'TituloDoctorado' => null,
                'AnosExperiencia' => null,
                'Telefono' => $tnTelefono,
            ]);
    

                 // Insertar en la tabla empresa
                $empresaId = DB::table('empresa')->insertGetId([
                    'Nombre' => $tcNombreEmpresa,
                    'NombreComercial' => $tcNombreEmpresa, // Puedes usar el mismo nombre si no hay nombre comercial
                    'Direccion' => null, // Opcional
                    'Descripcion' => $tcDescripcion,
                    'UrlImagen' => null, // Opcional
                    'UrlIcono' => null, // Opcional
                    'Estado' => 1, // Estado activo
                    'TipoEmpresa' => null, // Opcional
                    'TamañoEmpresa' => null, // Opcional
                    'AñoFundacion' => $tnAnoFundacion,
                    'EmpresaCodigo' => uniqid(), // Generar un código único para la empresa
                    'Acercade' => null, // Opcional
                    'Telefono' => $tnTelefono,
                    'Correo' => $tcCorreo, // Correo del usuario
                    'Pais' => null, // Opcional
                    'Ciudad' => null, // Opcional
                   // 'Usuario' => $usuarioId, // Asociar la empresa al usuario creado
                ]);

                $ultimoSerial = DB::table('usuarioempresa')
                ->where('Usuario', $usuarioId)
                ->max('Serial');

            // Calcular el nuevo Serial
            $nuevoSerial = $ultimoSerial ? $ultimoSerial + 1 : 1;

            // Insertar en la tabla usuarioempresa
            DB::table('usuarioempresa')->insert([
                'Usuario' => $usuarioId,
                'Serial' => $nuevoSerial,
                'Empresa' => $empresaId,
                'Estado' => 1, // Estado activo
            ]);


            // Confirmar la transacción (commit)
            DB::commit();
    
            // Armar la respuesta
            $oPaquete = [
                'error' => false,
                'message' => 'Empresa creada con éxito.',
                'values' => [
                    'usuarioId' => $empresaId,
                ]
            ];
        }
        catch (\Throwable $ex) {
            // Revertir la transacción (rollback) en caso de error
            DB::rollBack();
    
            // Armar la respuesta de error
            $oPaquete = [
                'error' => true,
                'message' => $ex->getMessage(),
                'values' => null,
                "codigoerror"=>2
            ];
        }
    
        // Retornar la respuesta en formato JSON
        return response()->json($oPaquete);
    }


    
    public function crearempleosempresa(Request $request){
        /*
        datos a enviar  
{tcTitulo: '', tcDescripcion: '', tcDescripcionLarga: '', tcFechaVencimiento: '', tnSalario: 0, …}
taHabilidades
: 
[]
taIdiomas
: 
[]
taRequerimientos
: 
[]
taResponsabilidades
: 
[]
tcDescripcion
: 
""
tcDescripcionLarga
: 
""
tcDireccion
: 
""
tcFechaVencimiento
: 
""
tcTitulo
: 
""
tnCategoria
: 
0
tnSalario
: 
0
tnTiempoExperiencia
: 
""
tnTipoEmpleo
: 
0
[[Prototype]]
: 
Object
        
        */
        
        
        // Recuperar los datos del request
        $tcTitulo = $request->input('tcTitulo');
        $tcDescripcion = $request->input('tcDescripcion');
        $tcDescripcionLarga = $request->input('tcDescripcionLarga');
        $tcFechaVencimiento = $request->input('tcFechaVencimiento');
        $tnSalario = $request->input('tnSalario');
        $tnTiempoExperiencia = $request->input('tnTiempoExperiencia');

        $tnTipoEmpleo = $request->input('tnTipoEmpleo');
        $tcDireccion = $request->input('tcDireccion');
        $tnCategoria = $request->input('tnCategoria');
    
       
    
        try {
            // Iniciar una transacción
            DB::beginTransaction();
    
            // Insertar en la tabla usuario
            $empleoId = DB::table('empleo')->insertGetId([
                //'Empleo' => 1,
                'Titulo' => $tcTitulo,
                'Descripcion' => $tcDescripcion,
                'FechaVencimiento' => $tcFechaVencimiento,
                'SalarioAproximado' => $tnSalario,
                'Empresa' => $tnEmpresa,
                'TipoEmpleo' => $tnTipoEmpleo,
                'FechaPublicacion' => Date("y-m-d"),
                'Ubicacion' => $tcDireccion,
                'Lat' => '-19.0333',
                'Lng' => '-65.2627',
                'Categoria' => $tnCategoria,
                'TiempoExperiencia' => $tnTiempoExperiencia,
                'Estado' => 1,
                'CodigoEmpleo' => uniqid(), 
                'DescripcionLarga' => $tcDescripcionLarga
            ]);
    
            // Insertar en la tabla candidato
            DB::table('candidato')->insert([
                'Nombre' => $tcNombre . ' ' . $tcApellidos,
                'Profesion' => $tcProfesion,
                'Estado' => 1,
                'Usuario' => $usuarioId,
                'CandidatoCodigo' => uniqid(), // Generar un código único para el candidato
                'FechaNacimiento' => null,
                'Acercade' => null,
                'Pais' => null,
                'Ciudad' => null,
                'Sexo' => null,
                'TituloTecnico' => null,
                'TituloLicenciatura' => null,
                'TituloDiplomado' => null,
                'TituloMaestria' => null,
                'TituloDoctorado' => null,
                'AnosExperiencia' => null,
                'Telefono' => $tnTelefono,
            ]);
    

                 // Insertar en la tabla empresa
                $empresaId = DB::table('empresa')->insertGetId([
                    'Nombre' => $tcNombreEmpresa,
                    'NombreComercial' => $tcNombreEmpresa, // Puedes usar el mismo nombre si no hay nombre comercial
                    'Direccion' => null, // Opcional
                    'Descripcion' => $tcDescripcion,
                    'UrlImagen' => null, // Opcional
                    'UrlIcono' => null, // Opcional
                    'Estado' => 1, // Estado activo
                    'TipoEmpresa' => null, // Opcional
                    'TamañoEmpresa' => null, // Opcional
                    'AñoFundacion' => $tnAnoFundacion,
                    'EmpresaCodigo' => uniqid(), // Generar un código único para la empresa
                    'Acercade' => null, // Opcional
                    'Telefono' => $tnTelefono,
                    'Correo' => $tcCorreo, // Correo del usuario
                    'Pais' => null, // Opcional
                    'Ciudad' => null, // Opcional
                   // 'Usuario' => $usuarioId, // Asociar la empresa al usuario creado
                ]);

                $ultimoSerial = DB::table('usuarioempresa')
                ->where('Usuario', $usuarioId)
                ->max('Serial');

            // Calcular el nuevo Serial
            $nuevoSerial = $ultimoSerial ? $ultimoSerial + 1 : 1;

            // Insertar en la tabla usuarioempresa
            DB::table('usuarioempresa')->insert([
                'Usuario' => $usuarioId,
                'Serial' => $nuevoSerial,
                'Empresa' => $empresaId,
                'Estado' => 1, // Estado activo
            ]);


            // Confirmar la transacción (commit)
            DB::commit();
    
            // Armar la respuesta
            $oPaquete = [
                'error' => false,
                'message' => 'Empresa creada con éxito.',
                'values' => [
                    'usuarioId' => $empresaId,
                ]
            ];
        }
        catch (\Throwable $ex) {
            // Revertir la transacción (rollback) en caso de error
            DB::rollBack();
    
            // Armar la respuesta de error
            $oPaquete = [
                'error' => true,
                'message' => $ex->getMessage(),
                'values' => null,
                "codigoerror"=>2
            ];
        }
    
        // Retornar la respuesta en formato JSON
        return response()->json($oPaquete);
    }





}
