<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::create('solicitud_pasos', function (Blueprint $t) {
        $t->id();

        $t->foreignId('solicitud_id')
            ->constrained('solicitudes')   
            ->cascadeOnDelete();

        $t->foreignId('plantilla_paso_id')
            ->constrained('plantilla_pasos')
            ->cascadeOnDelete();

        $t->boolean('hecho')->default(false);
        $t->timestamp('done_at')->nullable();
        $t->foreignId('done_by')->nullable()
            ->constrained('users')->nullOnDelete();

        $t->text('notas')->nullable();
        $t->timestamps();

        $t->unique(['solicitud_id','plantilla_paso_id']);
    });
}
    public function down(): void
    {
        Schema::dropIfExists('solicitud_pasos');
    }
};

