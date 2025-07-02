namespace App\Services;

use App\Mail\GenericMessageMail;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function enviarCorreo($destinatario, $asunto, $mensaje)
    {
        $contenido = [
            'asunto' => $asunto,
            'mensaje' => $mensaje
        ];

        try {
            Mail::to($destinatario)->send(new GenericMessageMail($contenido));
            return ['status' => true, 'mensaje' => 'Correo enviado'];
        } catch (\Exception $e) {
            return ['status' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }
}
