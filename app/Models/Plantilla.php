<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plantilla extends Model
{
    protected $fillable = ['nombre','descripcion'];

    public function pasos(): HasMany {
        return $this->hasMany(PlantillaPaso::class)->orderBy('numero');
    }
}