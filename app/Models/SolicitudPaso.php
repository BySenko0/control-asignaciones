<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudPaso extends Model
{
    protected $fillable = [
        'solicitud_id','plantilla_paso_id','hecho','done_at','done_by','notas'
    ];

    public function solicitud(){ return $this->belongsTo(Solicitud::class); }
    public function paso(){ return $this->belongsTo(PlantillaPaso::class,'plantilla_paso_id'); }
    public function doneBy(){ return $this->belongsTo(User::class,'done_by'); }
}