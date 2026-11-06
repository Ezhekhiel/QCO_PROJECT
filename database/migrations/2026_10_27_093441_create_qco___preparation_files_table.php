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
        Schema::create('qco___preparation_files', function (Blueprint $table) {
            $table->id();
            // Kolom preparation_id yang terhubung ke id tabel qco___preparation
            $table->foreignId('preparation_id')->constrained('qco___preparation')->onDelete('cascade');
            $table->string('file'); // Menyimpan nama file atau path file
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qco___preparation_files');
    }
};
