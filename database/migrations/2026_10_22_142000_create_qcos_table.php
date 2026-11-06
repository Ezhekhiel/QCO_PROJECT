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
        Schema::create('qco', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke kolom 'nik' di tabel 'karyawans'
            $table->string('nik');
            $table->foreign('nik')->references('nik')->on('users')->onDelete('cascade');
            $table->date('change_over_date');
            $table->string('model');
            $table->date('completion_date');
            $table->integer('cell');
            $table->text('description')->nullable();
            $table->string('stage');
            $table->integer('completion');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qco');
    }
};
