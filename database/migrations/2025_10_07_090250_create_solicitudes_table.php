<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            // Cliente (tabla clientes_asignaciones)
            $table->foreignId('cliente_id')
                  ->constrained('clientes_asignaciones')   // FK -> clientes_asignaciones.id
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            // Asignado a (users) - opcional
            $table->foreignId('asignado_a')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            // Datos de la solicitud
            $table->string('no_serie', 120)->nullable()->index();
            $table->string('dispositivo', 150);      // ej: Laptop, PC
            $table->string('modelo', 150)->nullable();
            $table->string('tipo_servicio', 150);    // ej: mantenimiento, cambio RAM
            $table->enum('estado', ['pendiente','en_proceso','finalizado'])
                  ->default('pendiente')
                  ->index();
            $table->text('descripcion')->nullable();

            $table->timestamps();

            // Índices útiles
            $table->index(['cliente_id','estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
