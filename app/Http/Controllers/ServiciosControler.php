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


            /*
            echo '<pre>'; 
            print_r($user ) ;
            echo '</pre>';*/

            // Si el token es válido, traer las empresas
            //$companies = Company::all(); // Aquí puedes agregar cualquier consulta que necesites

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
