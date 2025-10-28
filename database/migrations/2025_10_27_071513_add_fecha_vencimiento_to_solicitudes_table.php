<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->nullable()->after('estado');
        });
    }
    public function down(): void {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn('fecha_vencimiento');
        });
    }
};