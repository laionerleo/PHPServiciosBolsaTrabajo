<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject; // Importar JWTSubject para implementar

class usuario extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'usuario';  // Asegúrate de que este nombre coincida con el de tu tabla
    protected $primaryKey = 'Usuario'; // Cambia 'company_id' por el nombre correcto de tu campo de clave primaria
    protected $fillable = [
        'NombreCompleto',
        'Correo',
        'Contraseña',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'Contraseña',
        //'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
   /* protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    */
     /**
     * Implementación de JWTSubject: devolver el identificador (normalmente el ID).
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();  // Devuelve la clave primaria (ID)
    }

    /**
     * Implementación de JWTSubject: devolver cualquier reclamo personalizado del JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];  // Puedes agregar reclamos personalizados aquí si los necesitas
    }
}
