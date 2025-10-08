<?php
// app/Models/PlantillaPaso.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaPaso extends Model
{
    protected $fillable = ['plantilla_id','numero','titulo'];

    public function plantilla(): BelongsTo {
        return $this->belongsTo(Plantilla::class);
    }
}
