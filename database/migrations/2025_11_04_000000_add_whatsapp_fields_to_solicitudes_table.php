<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('whatsapp_ticket_status', 30)->nullable()->after('ticket_pdf_path');
            $table->timestamp('whatsapp_ticket_sent_at')->nullable()->after('whatsapp_ticket_status');
            $table->text('whatsapp_ticket_error')->nullable()->after('whatsapp_ticket_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_ticket_status',
                'whatsapp_ticket_sent_at',
                'whatsapp_ticket_error',
            ]);
        });
    }
};
