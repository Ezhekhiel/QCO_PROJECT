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
        Schema::create('qco___preparation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_qco')->constrained('qco')->onDelete('cascade');
            $table->foreignId('id_list')->constrained('qco___preparation_list')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qco___preparation');
    }
};
