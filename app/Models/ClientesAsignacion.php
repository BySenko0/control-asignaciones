<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesAsignacion extends Model
{
    protected $table = 'clientes_asignaciones';

    protected $fillable = [
        'nombre_cliente',
        'nombre_empresa',
        'direccion',
        'responsable',
        'rfc',
        'imagen',
        'correo_empresa',
    ];
}
