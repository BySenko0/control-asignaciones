<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ClientesAsignacion extends Model
{
    protected $table = 'clientes_asignaciones';

    protected $appends = [
        'imagen_url',
    ];

    protected $fillable = [
        'nombre_cliente',
        'nombre_empresa',
        'direccion',
        'responsable',
        'telefono',
        'rfc',
        'imagen',
        'correo_empresa',
    ];

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'cliente_id'); // FK en solicitudes
    }

    public function getImagenUrlAttribute(): ?string
    {
        $path = $this->imagen;
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/app/public/')) {
            $cleanPath = substr($cleanPath, strlen('storage/app/public/'));
        }
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, strlen('storage/'));
        }

        return Storage::disk('public')->url($cleanPath);
    }
}