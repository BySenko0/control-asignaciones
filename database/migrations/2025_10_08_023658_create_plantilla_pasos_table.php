<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plantilla_pasos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained('plantillas')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero'); // 1..15
            $table->string('titulo');
            $table->timestamps();

            $table->unique(['plantilla_id','numero']);
        });
    }
    public function down(): void { Schema::dropIfExists('plantilla_pasos'); }
};