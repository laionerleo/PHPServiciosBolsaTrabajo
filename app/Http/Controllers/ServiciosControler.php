<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB; // Usamos DB para consultas directas
use Illuminate\Support\Facades\Hash;
use App\Models\usuario;
use GuzzleHttp\Client;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericMessageMail; // El mailable que ya hicimos
use App\Models\Utils\mPaqueteEmpleo; // Asegúrate de que la ruta sea correcta



class ServiciosControler extends Controller
{
    //
       // Método para login
       // Método para login (sin usar modelos)


    public function login(Request $request)
    {
        $loPaquete = new mPaqueteEmpleo(1, 0, "Error ...", null);
        $credentials = $request->only('email', 'password');

        try {
            // Validar si el correo existe en la base de datos
            $loUsuario = usuario::where('Correo', $credentials['email'])->first();
            // return $loUsuario;

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
            $tnCurriculum = $request->input('tnCurriculum');
            $tcCodigoEmpleo = $request->input('tcCodigoEmpleo');
            // Validar el token
            $loUsuario = JWTAuth::parseToken()->authenticate();
            $lnUsuario =$loUsuario->Usuario;
            $loCandidato = DB::table('candidato')
                            ->where('Usuario', '=', $lnUsuario)
                            ->first();
            $lnCandidato=$loCandidato->Candidato;

            $tcCodigoEmpresa = $request->input('tcCodigoEmpresa');
            $loEmpleo = DB::table('empleo')
                            ->where('CodigoEmpleo', '=', $tcCodigoEmpleo)
                            ->first();
            $lnEmpleo=$loEmpleo->Empleo;
              // Insertar en la tabla usuario


              $ultimoSerial = DB::table('candidatoempleo')
                                ->where('Candidato',  $lnCandidato)
                                ->max('Serial');
                // Verificar si el candidato ya está postulando para el empleo
                $existePostulacion = DB::table('candidatoempleo')
                                        ->where('Candidato', $lnCandidato)
                                        ->where('Empleo', $lnEmpleo)
                                        ->exists();

                if ($existePostulacion) {
                    // Si ya existe la postulación, devolver respuesta de error
                    return response()->json([
                        'message' => 'Ya estás postulando a este empleo.',
                        'error' => true,
                        'Datos' => 0, // Puede indicar que la acción no se realizó
                    ]);
                } else {
                    // Si no existe, proceder con la inserción
                    $ultimoSerial = DB::table('candidatoempleo')
                                    ->where('Candidato', $lnCandidato)
                                    ->max('Serial');

                    // Insertar la nueva postulación
                    $insercionExitosa = DB::table('candidatoempleo')->insert([
                        'Candidato' => $lnCandidato,
                        'Serial' => $ultimoSerial + 1,
                        'Empleo' => $lnEmpleo,
                        'Estado' => 1,
                        'FechaPostulacion' => now(),
                        'Curriculum' => $tnCurriculum
                    ]);

                    if ($insercionExitosa) {
                        return response()->json([
                            'message' => 'Postulación realizada con éxito.',
                            'error' => false,
                            'Datos' => 1, // Indica que la inserción fue exitosa
                        ]);
                    } else {
                        return response()->json([
                            'message' => 'Error al realizar la postulación.',
                            'error' => true,
                            'Datos' => 0, // Indica que hubo un error
                        ]);
                    }
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
            ->join('usuarioempresa as ue', 'e.Empresa', '=', 'ue.Empresa')
            ->select(
                'e.Empleo', 'e.Titulo', 'e.Descripcion', 'e.FechaVencimiento', 'e.SalarioAproximado',
                'e.FechaPublicacion', 'e.Ubicacion', 'e.Lat', 'e.Lng',
                'e.Categoria', 'e.TiempoExperiencia',
                'c.Nombre as CategoriaNombre',
                'emp.Nombre as EmpresaNombre', 'emp.Descripcion as EmpresaDescripcion',
                'te.Nombre as TipoEmpleoNombre', 'tec.Titulo as TiempoExperienciaTitulo',
                'e.CodigoEmpleo',
                'e.Estado'
            )
            ->where('ue.Usuario', '=', $lnUsuario)
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
            ->leftJoin('estadopostulacion as ep', 'ep.EstadoPostulacion', '=', 'ce.Estado') // Unir con candidato
            ->select(
                'e.Empleo', 'e.Titulo', 'e.Descripcion', 'e.FechaVencimiento', 'e.SalarioAproximado',
                'e.FechaPublicacion', 'e.Ubicacion', 'e.Lat', 'e.Lng',
                'e.Categoria', 'e.TiempoExperiencia',
                'c.Nombre as CategoriaNombre',
                'emp.Nombre as EmpresaNombre', 'emp.Descripcion as EmpresaDescripcion',
                'te.Nombre as TipoEmpleoNombre', 'tec.Titulo as TiempoExperienciaTitulo',
                'e.CodigoEmpleo',
                'ce.FechaPostulacion', 'ce.Estado',
                'ep.Descripcion as EstadoDescripcion' // Información de la postulación

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


    public function listarempleosbycandidatomes(Request $request)
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
            ->whereMonth('ce.FechaPostulacion', now()->month) // Filtrar por el mes actual
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
        $loPaquete = new mPaqueteEmpleo(1, 0, "Error ...", null);
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
            $loPaquete->message = "El correo electrónico ya está registrado.";
            return response()->json($loPaquete);
        }

        try {
            // Iniciar una transacción
            DB::beginTransaction();

            // Insertar en la tabla usuario
             $datosUsuario=[
                'NombreCompleto' => $tcNombre . ' ' . $tcApellidos,
                'Correo' => $tcCorreo,
                'Telefono' => $tnTelefono,
                'Contraseña' => bcrypt($tcContraseña), // Encriptar la contraseña
                'Estado' => 1,
                'FechaCreacion' => now(),
            ];
            $usuarioId = DB::table('usuario')->insertGetId($datosUsuario);
            $usuariodato = usuario::where('Correo', $tcCorreo)->first();

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
            $token = JWTAuth::fromUser($usuariodato);
            DB::commit();

            // Armar la respuesta
            $loPaquete->error = 0;
            $loPaquete->status = 1;
            $loPaquete->message = "Candidato creada con éxito.";
            $loPaquete->values = [
                "token" => $token,
            ];
            return response()->json($loPaquete);
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


    public function crearcandidatoempresa(Request $request)
    {
        $loPaquete = new mPaqueteEmpleo(1, 0, "Error ...", null);
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
            $loPaquete->message = "El correo electrónico ya está registrado.";
            return response()->json($loPaquete);

        }

        try {
            // Iniciar una transacción
            DB::beginTransaction();

            // Insertar en la tabla usuario
            // datosUsuario=[
            //     'NombreCompleto' => $tcNombre . ' ' . $tcApellidos,
            //     'Correo' => $tcCorreo,
            //     'Telefono' => $tnTelefono,
            //     'Contraseña' => bcrypt($tcContraseña), // Encriptar la contraseña
            //     'Estado' => 1,
            //     'FechaCreacion' => now(),
            // ];
            $usuarioId = DB::table('usuario')->insertGetId([
                'NombreCompleto' => $tcNombre . ' ' . $tcApellidos,
                'Correo' => $tcCorreo,
                'Telefono' => $tnTelefono,
                'Contraseña' => bcrypt($tcContraseña), // Encriptar la contraseña
                'Estado' => 1,
                'FechaCreacion' => now(),
            ]);
            $usuariodato = usuario::where('Correo', $tcCorreo)->first();

            // return $usuariodato;

            // Insertar en la tabla candidato
            DB::table('candidato')->insert([
                'Nombre' => $tcNombre . ' ' . $tcApellidos,
                'Profesion' => $tcProfesion,
                'Estado' => 1,
                'Usuario' => $usuarioId,
                'CandidatoCodigo' => uniqid(), // Generar un código único para el candidato
                'FechaNacimiento' => null,
                'Acercade' => null,
                'Pais' => 1,
                'Ciudad' => 8,
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
                    'Pais' => 1, // Opcional
                    'Ciudad' => 8, // Opcional
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
            $token = JWTAuth::fromUser($usuariodato);
            DB::commit();

            // Armar la respuesta
            $loPaquete->error = 0;
            $loPaquete->status = 1;
            $loPaquete->message = "Empresa creada con éxito.";
            $loPaquete->values = [
                "usuarioId" => $empresaId,
                "token" => $token,
            ];
            return response()->json($loPaquete);

            // $oPaquete = [
            //     'error' => false,
            //     'message' => 'Empresa creada con éxito.',
            //     'values' => [
            //         'usuarioId' => $empresaId,
            //     ]
            // ];
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



      public function crearempleosempresa(Request $request)
    {



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
        $taHabilidades = $request->input('taHabilidades');
        $taIdiomas = $request->input('taIdiomas');
        $taResponsabilidades = $request->input('taResponsabilidades');
        $taRequerimientos = $request->input('taRequerimientos');
         $tnCiudad = $request->input('tnCiudad');

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
            $loEmpresa = DB::select("SELECT *
                                        FROM usuarioempresa ue, empresa e
                                        WHERE ue.Empresa=e.Empresa
                                        and ue.Usuario =$lnUsuario ");
            $lnEmpresa= $loEmpresa[0]->Empresa;



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
                'Empresa' => $lnEmpresa,
                'TipoEmpleo' => $tnTipoEmpleo,
                'FechaPublicacion' => Date("y-m-d"),
                'Ubicacion' => $tcDireccion,
                'Lat' => '-17.783269751313597',
                'Lng' => '-63.18217161546975',
                'Categoria' => $tnCategoria,
                'TiempoExperiencia' => $tnTiempoExperiencia,
                'Estado' => 1,
                'CodigoEmpleo' => uniqid(),
                'DescripcionLarga' => $tcDescripcionLarga,
                  'Ubicacion' => $tnCiudad
            ]);


            $tnSerial=0;
            if(count($taHabilidades) >0)
            {
                for ($i=0; $i <  count($taHabilidades) ; $i++) {
                    $tnHabilidades= $taHabilidades[$i];
                    $tnSerial=$tnSerial+1;
                   // Insertar en la tabla candidato
                DB::table('empleohabilidades')->insert([
                    'Empleos' => $empleoId,
                    'Serial' => $tnSerial,
                    'Habilidades' => $tnHabilidades,
                    'Estado' => 1
                ]);
            }
            }
            if(count($taRequerimientos) >0 )
            {
                                $tnSerial=0;
                for ($j=0; $j <count($taRequerimientos) ; $j++) {
                    $tnSerial=$tnSerial+1;
                    $tcRequerimiento=$taRequerimientos[$j];
                    DB::table('empleorequerimiento')->insert([
                        'Empleo' => $empleoId,
                        'Serial' => $tnSerial,
                        'Descripcion' => $tcRequerimiento,
                        'Estado' => 1
                    ]);

                }


            }


            if( count($taResponsabilidades) >0){
                $tnSerial=0;
                for ($k=0; $k < count($taResponsabilidades) ; $k++) {
                    # code...
                    $tnSerial=$tnSerial+1;
                    $tcResponsabilidades=$taResponsabilidades[$k];
                    DB::table('empleoresponsabilidades')->insert([
                        'Empleo' => $empleoId,
                        'Serial' => $tnSerial,
                        'Descripcion' => $tcResponsabilidades,
                        'Estado' => 1
                    ]);

                }

            }






            // Confirmar la transacción (commit)
            DB::commit();

            // Armar la respuesta
            $oPaquete = [
                'error' => false,
                'message' => 'Empleo  creado con éxito.',
                'values' => [
                    'Empleo' => $empleoId,
                ]
            ];
        }
        catch (\Throwable $ex) {
            // Revertir la transacción (rollback) en caso de error
            DB::rollBack();

            // Armar la respuesta de error
            $oPaquete = [
                'error' => true,
                'message' => $ex->getMessage(). "Linea". $ex->getLine() ,
                'values' => null,
                "codigoerror"=>2
            ];
        }

        // Retornar la respuesta en formato JSON
        return response()->json($oPaquete);
    }









    public function crearcurriculumcandidato(Request $request)
    {


        // Recuperar los datos del request
        $tnCandidato = $request->input('tnCandidato');
        $tcTituloCurriculum = $request->input('tcTituloCurriculum');
        $PerfilProfesional = $request->input('PerfilProfesional');
        $tcCorreo = $request->input('tcCorreo');
        $tcTelefono = $request->input('tcTelefono');

        $tcNombreCompleto=$request->input('tcNombreCompleto');

        $tcDireccion = $request->input('tcDireccion');
        $taCertificaciones = $request->input('taCertificaciones');

        $taFormaciones = $request->input('taFormaciones');
        $taExperiencias = $request->input('taExperiencias');
        $taHabilidades = $request->input('taHabilidades');
        $taIdiomas = $request->input('taIdiomas');



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




        try {


        // Recuperar los datos del request
        $tnCandidato = $request->input('tnCandidatos');
        $tcTituloCurriculum = $request->input('tcTituloCurriculum');
        $PerfilProfesional = $request->input('PerfilProfesional');
        $tcCorreo = $request->input('tcCorreo');
        $tcTelefono = $request->input('tcTelefono');

        $tcNombreCompleto=$request->input('tcNombreCompleto');

        $tcDireccion = $request->input('tcDireccion');
        $taCertificaciones = json_decode($request->input('taCertificaciones'));

        $taFormaciones = json_decode($request->input('taFormaciones'));
        $taExperiencias = json_decode( $request->input('taExperiencias'));
        $taHabilidades =  json_decode($request->input('taHabilidades'));
        $taIdiomas =  json_decode( $request->input('taIdiomas'));
        $tcHtml = $request->input('tcHtml');





            // Retornar la respuesta en formato JSON
            //return response()->json(0);

            if ($request->hasFile('foto')) {
                $request->validate([
                    'foto' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                $file = $request->file('foto');

                // Generar nombre basado en fecha
                $fecha = now()->format('Ymd_His');
                $extension = $file->getClientOriginalExtension();
                $nombreArchivo = "foto_{$fecha}." . $extension;

                // Ruta de guardado
                $rutaCarpeta = public_path('curriculums');
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                }

                // Procesar la imagen con Intervention
                $imagen = Image::make($file);

                // Intentar quitar el fondo (esto requiere una imagen con fondo uniforme, o usar AI)
                // Aquí simplemente haremos el fondo blanco transparente (si existe)
                $imagen->encode('png'); // Para soportar transparencia

                // Crear fondo verde o amarillo
                $fondo = Image::canvas($imagen->width(), $imagen->height(), '#FFFF00'); // Verde (o '#FFFF00' para amarillo)

                // Insertar la imagen original encima del fondo
                $fondo->insert($imagen, 'center');

                // Guardar la imagen final
                $fondo->save($rutaCarpeta . '/' . $nombreArchivo);

                // Ruta para la base de datos
                $rutaFoto = 'curriculums/' . $nombreArchivo;
            } else {
                $rutaFoto = null;
               // echo "No se envió la foto.";
            }


            // Iniciar una transacción
            DB::beginTransaction();

            // Insertar en la tabla usuario
            $tnCurriculum = DB::table('curriculum')->insertGetId([
                //'Empleo' => 1,
                'Titulo' => $tcTituloCurriculum,
                'FechaCreacion' => Date("y-m-d"),
                'Candidato' => $tnCandidato,
                'Estado' => 1,
                'PerfilProfesional' => $PerfilProfesional,
                'Correo' => $tcCorreo,
                'Telefono' => @$tnTelefono,
                "Direccion"=>$tcDireccion,
                "NombreCompleto"=>$tcNombreCompleto,
                "Html"=>$tcHtml,
                "Foto"=>$rutaFoto

            ]);

            $tnSerial=0;
            if (!empty($taCertificaciones) && count($taCertificaciones) > 0) {
                for ($j = 0; $j < count($taCertificaciones); $j++) {
                    $tnSerial = $tnSerial + 1;
                    $tcNombre = $taCertificaciones[$j]->Nombre;
                    $tcNombreInstitucion = $taCertificaciones[$j]->NombreInstitucion;
                    $tcPeriodo = $taCertificaciones[$j]->Periodo;
                    $tcDescripcion = $taCertificaciones[$j]->Descripcion;

                    DB::table('curriculumcertificacion')->insert([
                        'Curriculum' => $tnCurriculum,
                        'Serial' => $tnSerial,
                        'Nombre' => $tcNombre,
                        'NombreInstitucion' => $tcNombreInstitucion,
                        'Periodo' => $tcPeriodo,
                        'Descripcion' => $tcDescripcion, // Corregido: clave única
                        'Estado' => 1
                    ]);
                }
            }

            $tnSerial=0;
            if (!empty($taHabilidades) && count($taHabilidades) > 0) {
                for ($i=0; $i <  count($taHabilidades) ; $i++) {
                    $tnSerial=$tnSerial+1;
                        $tnHabilidades= $taHabilidades[$i];
                    // Insertar en la tabla candidato
                    DB::table('curriculumhabilidades')->insert([
                        'Curriculum' => $tnCurriculum,
                        'Serial' => $tnSerial,
                        'Habilidades' => $tnHabilidades,
                        'Estado' => 1
                    ]);
                }
            }
            $tnSerial=0;
            if (!empty($taIdiomas) && count($taIdiomas) > 0) {
                for ($i=0; $i <  count($taIdiomas) ; $i++) {
                    $tcIdioma= $taIdiomas[$i];
                    $tnSerial=$tnSerial+1;
                    // Insertar en la tabla candidato
                    DB::table('curriculumidioma')->insert([
                        'Curriculum' => $tnCurriculum,
                        'Serial' => $tnSerial,
                        'Idioma' => $tcIdioma,
                        'Estado' => 1
                    ]);
                }
            }




            if(count($taExperiencias)>0)
            {
                $tnSerial=0;
                for ($j=0; $j <count($taExperiencias) ; $j++) {
                    # code...
                    $tnSerial=$tnSerial+1;
                    $tcNombre=$taExperiencias[$j]->Nombre;
                    $tcNombreInstitucion=$taExperiencias[$j]->NombreInstitucion;
                    $tcPeriodo=$taExperiencias[$j]->Periodo;
                    $tcDescripcion=$taExperiencias[$j]->Descripcion;

                    DB::table('curriculumexperiencialaboral')->insert([
                        'Curriculum' => $tnCurriculum,
                        'Serial' => $tnSerial,
                        'Titulo' => $tcNombre,
                        'NombreEmpresa' => $tcNombreInstitucion,
                        'Periodo' => $tcPeriodo,
                        'Descripcion' => $tcDescripcion ,
                        'Estado' => 1

                    ]);

                }

            }

            if(count($taFormaciones)>0)
            {
                $tnSerial=0;
                for ($k=0; $k <count($taFormaciones) ; $k++) {
                    # code...
                    $tnSerial=$tnSerial+1;
                    $tcNombre=$taFormaciones[$k]->Nombre;
                    $tcNombreInstitucion=$taFormaciones[$k]->NombreInstitucion;
                    $tcPeriodo=$taFormaciones[$k]->Periodo;
                    $tcDescripcion=$taFormaciones[$k]->Descripcion;

                    DB::table('curriculumformacion')->insert([
                        'Curriculum' => $tnCurriculum,
                        'Serial' => $tnSerial,
                        'Nombre' => $tcNombre,
                        'Institucion' => $tcNombreInstitucion,
                        'Periodo' => $tcPeriodo,
                        'Estado' => 1

                    ]);

                }

            }




            // Confirmar la transacción (commit)
            DB::commit();

            // Armar la respuesta
            $oPaquete = [
                'error' => false,
                'message' => 'Empleo  creado con éxito.',
                'values' => [
                    'Curriculum' => $tnCurriculum,
                ]
            ];
        }
        catch (\Throwable $ex) {
            // Revertir la transacción (rollback) en caso de error
            DB::rollBack();

            // Armar la respuesta de error
            $oPaquete = [
                'error' => true,
                'message' => $ex->getMessage() ."  ".$ex->getLine() .json_encode($taFormaciones),
                'values' => null,
                "codigoerror"=>2
            ];
        }

        // Retornar la respuesta en formato JSON
        return response()->json($oPaquete);
    }



    // Método para validar token y traer los datos
    public function listarempleosbyempresames(Request $request)
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
            ->join('usuarioempresa as ue', 'e.Empresa', '=', 'ue.Empresa')
            ->select(
                'e.Empleo', 'e.Titulo', 'e.Descripcion', 'e.FechaVencimiento', 'e.SalarioAproximado',
                'e.FechaPublicacion', 'e.Ubicacion', 'e.Lat', 'e.Lng',
                'e.Categoria', 'e.TiempoExperiencia',
                'c.Nombre as CategoriaNombre',
                'emp.Nombre as EmpresaNombre', 'emp.Descripcion as EmpresaDescripcion',
                'te.Nombre as TipoEmpleoNombre', 'tec.Titulo as TiempoExperienciaTitulo',
                'e.CodigoEmpleo',
                'e.Estado'
            )
            ->whereMonth('e.FechaPublicacion', now()->month) // Filtra por el mes actual
            ->whereYear('e.FechaPublicacion', now()->year) // Filtra por el año actual
            ->where('ue.Usuario', '=', $lnUsuario)
            ->get();





            return response()->json([
                'message' => 'empleos de la empresa del mes ',
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



    public function listarcurriculumbycandidato(Request $request)
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
            $loCandidato = DB::table('candidato')
                            ->where('Usuario', '=', $lnUsuario)
                            ->first();
            $lnCandidato=$loCandidato->Candidato;




            $empleos = DB::table('curriculum as cu')
            ->leftJoin('candidato as cand', 'cu.Candidato', '=', 'cand.Candidato') // Unir con candidato

            ->select(
                'cu.Curriculum', 'cu.Titulo', 'cu.FechaCreacion', 'cu.Pagado',
            )
            ->where('cu.Candidato', '=', $lnCandidato) // Filtrar por el Usuario asociado al candidato
            ->get();


            return response()->json([
                'message' => 'lista de curriculun canditado ',
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





    public function generarpago(Request $request)
    {


              // Recuperar los datos del request
            $tcCorreo = $request->input('tcCorreo');
            $tnTelefono = $request->input('tnTelefono');
            $tcNombreUsuario = $request->input('tcNombreUsuario');
            $tnCiNit = $request->input('tnCiNit');
            $Curriculum = $request->input('Curriculum');
            $Empleo = $request->input('Empleo');
            if(!is_null($Curriculum))
            {
                $tcNumeroPago="CV-".$Curriculum;
            }else{
                $tcNumeroPago="EM-".$Empleo;
            }





            $lcComerceID = "4be84111a613654b362415e563cb7607df7b203b5d303802a8a546061bbc7847";
            $lcUrlCallBack = "http://serviciostigomoney.pagofacil.com.bo/api/servicio/callbacktest";

            $lcUrlTransaccion = 'http://serviciostigomoney.pagofacil.com.bo/api/servicio/generarqrv2';
            $lcUrlReturn="";
            // $lcUrlTransaccion = 'http://serviciostigomoney.pagofacil.com.bo/api/servicio/generarqrv';
            $laBodyTransaccion = [
                "tnCliente" => 9,
                "tnEmpresa" => 263,
                "tcCommerceID" => $lcComerceID,
                "tnMoneda" => 2, //$lnMoneda,
                "tnMetodoPago" => 4,
                "tnTelefono" => $tnTelefono,
                "tcNombreUsuario" => $tcNombreUsuario,
                "tnCiNit" => $tnCiNit,
                "tcNroPago" => $tcNumeroPago,
                "tnMontoClienteEmpresa" => "0.01",
                //"tnMontoComision" =>,
                "tcPeriodo" => "Chekout",
                "tcCorreo" => $tcCorreo,
                "tcUrlCallBack" => $lcUrlCallBack,
                "tcUrlReturn" => $lcUrlReturn,
                "taPedidoDetalle" => @$laPedidoDetalle
            ];

            try {

                $loClientTransaccion=new Client();
                $laHeaderTransaccion = ['Accept' => 'application/json'];
                        $loResponseTransaccion = $loClientTransaccion->post($lcUrlTransaccion, [
                            'headers' => $laHeaderTransaccion,
                            'json' => $laBodyTransaccion
                        ]);

                        // Procesar la respuesta
                        $lcResultTransaccion = json_decode($loResponseTransaccion->getBody()->getContents());
                          // Verificar si tiene el atributo "values"
                            if (!isset($lcResultTransaccion->values)) {
                                return response()->json([
                                    'message' => 'La respuesta no contiene valores.',
                                    'error' => true,
                                    'Datos' => [$lcResultTransaccion, $laBodyTransaccion]
                                ], 400);
                            }
                        $laObjetoQr = explode(";", $lcResultTransaccion->values);

                        $lnTransaccionDePago=$laObjetoQr[0];
                        $lcQRBASE64=json_decode($laObjetoQr[1]);





                    return response()->json([
                        'message' => 'lista de curriculun canditado ',
                        'error' => false,
                        'Datos' => [
                            "TransaccionDePago"=>$lnTransaccionDePago,
                            "lcQRBASE64"=>$lcQRBASE64->qrImage
                        ]
                    ]);
                } catch (\Exception $e) {
                    // Si no se puede autenticar el token, devolver error
                    return response()->json([
                        'message' => 'Error catch '. $e->getMessage(). "  -  ".'linea '. $e->getLine(),
                        'error' => true,
                        "codigoerror"=>2,
                    ]);
                }


    }


        // metodo para consultar a pago facil
   public function consultarEstado(Request $request)
   {


       try {


           $lnNumeroTransaccion = $request->tnTransaccionDePago; // Asegúrate de tener el nombre correcto del parámetro
           $tnCurriculum = $request->tnCurriculum; // Asegúrate de tener el nombre correcto del parámetro
           $tnEmpleo = $request->tnEmpleo; // Asegúrate de tener el nombre correcto del parámetro





           // return response()->json($request->all());
           // Cliente HTTP para hacer la solicitud
           $client = new Client();

           // URL del servicio externo para consultar estado, incluyendo el número de transacción
           $url = 'http://serviciostigomoney.pagofacil.com.bo/api/servicio/consultartransaccion';

           // Hacer la solicitud GET al servicio externo
           $response = $client->post($url,[
               'json' => array(
           'TransaccionDePago' => "$lnNumeroTransaccion"
           )]);

           $responseData = json_decode($response->getBody()->getContents());




               // Verificar si values no está vacío y contiene los campos necesarios
              if (!empty($responseData->values) && isset($responseData->values->MetodoPago) && isset($responseData->values->EstadoTransaccion))
               {
                   $metodoPago = $responseData->values->MetodoPago;
                   $estadoTransaccion = $responseData->values->EstadoTransaccion;

                   // Actualizar estado de la transacción si el estado es 2
                   if (  $estadoTransaccion == 2) {

                    if(!is_null($tnCurriculum))
                    {
                        $affectedRows = DB::table('curriculum')
                        ->where('Curriculum', $tnCurriculum)  // Asegúrate de que $tnCandidato tenga el ID correcto
                        ->update([
                            'Pagado' => 1  // Campo actualizado a 1
                        ]);
                    }else{
                        $affectedRows = DB::table('empleo')
                        ->where('Empleo',$tnEmpleo)  // Asegúrate de que $tnCandidato tenga el ID correcto
                        ->update([
                            'Pagado' => 1  // Campo actualizado a 1
                        ]);
                    }
                        // Actualizar en la tabla curriculum donde el Candidato coincide


                   }

                   // Actualizar estado de la transacción si el estado es 4
                   if ($estadoTransaccion == 4) {

                   }

                   // Manejar retiro pago si el método de pago es 30 y el estado de la transacción es 2

               } else {
                   // Guardar log si values está vacío o faltan campos

               }
                   // $loPaquete->values = ["tnTransaccionRetiroPago"=>$tnIdRetiro, "toConsultarTransaccionPago"=>$responseData->values ];
           return response()->json($responseData);



       } catch (\Exception $e) {
           // Captura cualquier excepción y devuelve un error
           // Log::error('Error al consultar estado de transacción: ' . $e->getMessage(), [
           //     'stack' => $e->getTraceAsString()
           // ]);

           return response()->json([
               'error' => 1,
               'status' => 0,
               'message' => 'Ocurrió un error al consultar el estado de la transacción'.$e->getLine().$e->getMessage(),
               'values' => null
           ], 500);
       }
   }




    // Método para validar token y traer los datos
    public function listarpostulantesbyempresa(Request $request)
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

            $candidatos = DB::table('candidato as c')
            ->join('candidatoempleo as ce', 'c.Candidato', '=', 'ce.Candidato')
            ->join('empleo as e', 'e.Empleo', '=', 'ce.Empleo')
            ->join('usuarioempresa as ue', 'e.Empresa', '=', 'ue.Empresa')
            ->where('ue.Usuario', $lnUsuario)
            ->select('c.Candidato', 'c.Nombre', 'c.Profesion', 'e.Empleo' , 'ce.FechaPostulacion')
            ->distinct()
            ->get();


            return response()->json([
                'message' => 'candidatos o postiulanes  ',
                'error' => false,
                'Datos' => $candidatos
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




    
    // Método para validar token y traer los datos
    public function graficasDashboard(Request $request)
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
            
            $postulacionesPorMes = DB::table('candidato as c')
                                    ->join('candidatoempleo as ce', 'c.Candidato', '=', 'ce.Candidato')
                                    ->join('empleo as e', 'e.Empleo', '=', 'ce.Empleo')
                                    ->join('usuarioempresa as ue', 'e.Empresa', '=', 'ue.Empresa')
                                    ->where('ue.Usuario', $lnUsuario)
                                    ->select(
                                        DB::raw('MONTH(ce.FechaPostulacion) as mes'),
                                        DB::raw('COUNT(*) as cantidad')
                                    )
                                    ->groupBy(DB::raw('YEAR(ce.FechaPostulacion)'), DB::raw('MONTH(ce.FechaPostulacion)'))
                                   // ->orderBy('anio')
                                    ->orderBy('mes')
                                    ->get();


            $empleosPorMes = DB::table('empleo as e')
                        ->leftJoin('usuarioempresa as ue', 'e.Empresa', '=', 'ue.Empresa')
                        ->select(
                         
                            DB::raw('MONTH(e.FechaPublicacion) as mes'),
                            DB::raw('COUNT(*) as cantidad')
                        )
                        ->where('ue.Usuario', '=', $lnUsuario)
                        ->groupBy(DB::raw('YEAR(e.FechaPublicacion)'), DB::raw('MONTH(e.FechaPublicacion)'))
                        //->orderBy('anio')
                        ->orderBy('mes')
                        ->get();


                            
                $postulacionesPorEmpleo = DB::table('candidato as c')
                                            ->join('candidatoempleo as ce', 'c.Candidato', '=', 'ce.Candidato')
                                            ->join('empleo as e', 'e.Empleo', '=', 'ce.Empleo')
                                            ->join('usuarioempresa as ue', 'e.Empresa', '=', 'ue.Empresa')
                                            ->where('ue.Usuario', $lnUsuario)
                                            ->select(
                                                'e.Titulo as empleo', // o puedes usar 'e.Empleo' si prefieres el ID
                                                DB::raw('COUNT(*) as cantidad')
                                            )
                                            ->groupBy('e.Empleo', 'e.Titulo')
                                            ->orderByDesc('cantidad')
                                            ->get();

                                $postulacionesPorEstado = DB::table('candidato as c')
                                            ->join('candidatoempleo as ce', 'c.Candidato', '=', 'ce.Candidato')
                                            ->join('empleo as e', 'e.Empleo', '=', 'ce.Empleo')
                                            ->join('usuarioempresa as ue', 'e.Empresa', '=', 'ue.Empresa')
                                            ->where('ue.Usuario', $lnUsuario)
                                            ->select(
                                                DB::raw("
                                                    CASE ce.Estado
                                                        WHEN 1 THEN 'Pendiente'
                                                        WHEN 2 THEN 'Visto'
                                                        WHEN 3 THEN 'En Espera'
                                                        ELSE 'Desconocido'
                                                    END as estado_nombre
                                                "),
                                                DB::raw('COUNT(*) as cantidad')
                                            )
                                            ->groupBy('ce.Estado')
                                            ->orderBy('ce.Estado')
                                            ->get();

        

            return response()->json([
                'message' => 'candidatos o postiulanes  ',
                'error' => false,
                'Datos' => ["PostulantesPorMes"=>$postulacionesPorMes , "EmpleosPorMes"=>$empleosPorMes , "postulacionesPorEmpleo"=>$postulacionesPorEmpleo ,"candidatosporempleo"=>$postulacionesPorEstado  ]
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






         // metodo para consultar a pago facil
   public function enviarmensajecontactenos(Request $request)
       {
         $lcMensaje = "📩 Nuevo mensaje de contacto:\n\n";
    $lcMensaje .= "👤 Nombre completo: " . $request->input('tcNombreCompleto') . "\n";
    $lcMensaje .= "🏢 Empresa: " . $request->input('tcNombreEmpresa') . "\n";
    $lcMensaje .= "✉️ Correo: " . $request->input('tcCorreo') . "\n";
    $lcMensaje .= "✉️ Telefono: " . $request->input('tcTelefono') . "\n";
    $lcMensaje .= "📝 Mensaje: " . $request->input('tcMensaje') . "\n";
    $le;

        try {
            Mail::to("salvadorsuarez@employnx.com")->send(
                new GenericMessageMail("Nueva consulta de employnx", $lcMensaje)
            );

            Mail::to($request->input('tcCorreo'))->send(
                new GenericMessageMail(
                    "Hemos recibido su solicitud de información",
                    "Estimado/a, hemos recibido correctamente su mensaje. Agradecemos su interés y en breve uno de nuestros representantes se pondrá en contacto con usted para brindarle la información solicitada. 

            Atentamente, 
            El equipo de EMPLOYNX"
                )
            );
            return response()->json([
                'status' => true,
                'mensaje' => 'Correo enviado con éxito.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'mensaje' => 'Error al enviar: ' . $e->getMessage()
            ], 500);
        }
    }


    //mandarcodigo

    public function enviarCodigo(Request $request)
{
    $correo = $request->input('correo');

    // Verificar si el correo existe en la base de datos
    $usuario = DB::table('usuario')->where('Correo', $correo)->first();

    if (!$usuario) {
        return response()->json(['error' => true, 'message' => 'Correo no registrado']);
    }

    // Generar un código aleatorio de 6 dígitos
    $codigo = rand(100000, 999999);

    // Guardar el código en la base de datos para ese correo
    DB::table('recuperacion_cuentas')->insert([
        'correo' => $correo,
        'codigo' => $codigo,
        'created_at' => now(),
    ]);

    // Mensaje para el correo interno
    $lcMensaje = "📩 Solicitud de recuperación de cuenta:\n\n";
    $lcMensaje .= "✉️ Correo del usuario: " . $correo . "\n";
    $lcMensaje .= "🔑 Código de verificación: " . $codigo . "\n";

    try {
        // Enviar correo al administrador (o a la dirección que prefieras)
        Mail::to("salvadorsuarez@employnx.com")->send(
            new GenericMessageMail("Nueva solicitud de recuperación de cuenta", $lcMensaje)
        );

        // Enviar correo al usuario con el código de recuperación
        Mail::to($correo)->send(
            new GenericMessageMail(
                "Recuperación de Cuenta",
                "Estimado/a, hemos recibido su solicitud para recuperar la cuenta. Utilice el siguiente código para restablecer su contraseña:\n\n🔑 Código: {$codigo}\n\nEste código es válido por 15 minutos. Si no ha solicitado la recuperación, ignore este mensaje."
            )
        );

        return response()->json([
            'error' => false,
            'message' => 'Se ha enviado un código de verificación a tu correo.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => 'Error al enviar el correo: ' . $e->getMessage()
        ], 500);
    }
}



public function actualizarContrasena(Request $request)
{
    $correo = $request->input('correo');
    $codigo = $request->input('codigo');
    $nuevaContrasena = $request->input('nuevaContrasena');

    // Verificar si el código y correo son correctos
    $registro = DB::table('recuperacion_cuentas')
                  ->where('correo', $correo)
                  ->where('codigo', $codigo)
                  ->first();

    if (!$registro) {
        return response()->json(['error' => true, 'message' => 'Código de verificación incorrecto']);
    }

    // Validar la nueva contraseña
    $request->validate([
        'nuevaContrasena' => 'required|min:6',
    ]);

    // Actualizar la contraseña en la base de datos
    DB::table('usuario')->where('Correo', $correo)->update([
        'Contraseña' => bcrypt($nuevaContrasena),
    ]);

    // Eliminar el código de la base de datos (opcional)
    DB::table('recuperacion_cuentas')->where('correo', $correo)->delete();

    return response()->json(['error' => false, 'message' => 'Contraseña actualizada correctamente']);
}



}

