<?php

namespace App\Models\Utils;


class mPaqueteEmpleo
{
    public $error;
    public $status;
    public $message = "";
    public $messageMostrar = "";
    public $messageSistema = "";
    public $values;

    function __construct($tnError, $tnEstado, $tcMensaje, $tcValues)
    {
        $this->error = $tnError;
        $this->status = $tnEstado;
        $this->message = $tcMensaje;
        $this->values = $tcValues;
    }
}
