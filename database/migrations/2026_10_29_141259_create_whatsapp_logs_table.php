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
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('target_group');         // Code group / JID
            $table->text('message');                // Isi pesan yang dikirim
            $table->string('message_id')->nullable(); // ID Pesan dari WhatsApp (untuk fitur hapus/tracking)
            $table->string('status')->default('sent'); // sent, failed, dll
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
