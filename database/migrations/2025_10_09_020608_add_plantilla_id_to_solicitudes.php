<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            // clave foránea a plantillas.id
            $table->foreignId('plantilla_id')
                  ->nullable()                
                  ->after('modelo')           
                  ->constrained('plantillas') 
                  ->nullOnDelete()            
                  ->cascadeOnUpdate();        
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plantilla_id'); // borra FK + columna
        });
    }
};
