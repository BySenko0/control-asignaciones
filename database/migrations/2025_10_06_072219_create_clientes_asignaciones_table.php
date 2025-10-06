<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes_asignaciones', function (Blueprint $table) {
            $table->id();

            $table->string('nombre_cliente', 150);
            $table->string('nombre_empresa', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('responsable', 150)->nullable();
            $table->string('rfc', 50)->nullable()->index();
            $table->string('imagen', 255)->nullable();
            $table->string('correo_empresa', 150)->nullable()->index();

            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes_asignaciones');
    }
};
