<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Crea la tabla solo si no existe (útil si quedó a medias en un intento previo)
        if (!Schema::hasTable('dispositivos_asignaciones')) {
            Schema::create('dispositivos_asignaciones', function (Blueprint $table) {
                $table->id();

                // users (rol virtuality)
                $table->foreignId('user_id')->nullable()
                      ->constrained('users')->nullOnDelete()->index();

                // (opcional) quién registró
                $table->foreignId('registrado_por')->nullable()
                      ->constrained('users')->nullOnDelete();

                // dispositivo | periferico
                $table->enum('categoria', ['dispositivo', 'periferico'])->index();

                // Catálogos (sus PK son INT, no BIGINT)
                $table->unsignedInteger('tipo_dispositivo_id')->nullable()->index();
                $table->unsignedInteger('tipo_periferico_id')->nullable()->index();

                // Datos del ítem
                $table->string('no_serie', 120)->nullable()->index();
                $table->string('marca', 120)->nullable();
                $table->string('modelo', 120)->nullable();
                $table->string('descripcion', 500)->nullable();

                $table->enum('estado', ['disponible','asignado','en_servicio','dado_baja'])
                      ->default('disponible')->index();

                $table->timestamp('fecha_asignacion')->nullable();
                $table->timestamp('fecha_retorno')->nullable();

                $table->softDeletes();
                $table->timestamps();
            });

            // Llaves foráneas hacia catálogos con PK personalizadas
            Schema::table('dispositivos_asignaciones', function (Blueprint $table) {
                $table->foreign('tipo_dispositivo_id')
                      ->references('id_tipo_dispositivo')
                      ->on('tipo_dispositivos')
                      ->nullOnDelete();

                $table->foreign('tipo_periferico_id')
                      ->references('id_tipo')
                      ->on('tipo_perifericos')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('dispositivos_asignaciones')) {
            Schema::table('dispositivos_asignaciones', function (Blueprint $table) {
                // soltar FKs si existen
                try { $table->dropForeign(['tipo_dispositivo_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['tipo_periferico_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['registrado_por']); } catch (\Throwable $e) {}
            });

            Schema::dropIfExists('dispositivos_asignaciones');
        }
    }
};
