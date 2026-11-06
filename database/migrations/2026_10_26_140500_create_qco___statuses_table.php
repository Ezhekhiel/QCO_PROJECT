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
        Schema::create('qco___status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_qco')->constrained('qco')->onDelete('cascade');
            $table->date('actual_change_over')->nullable();
            $table->string('status')->default("Not Yet");
            $table->timestamps();
        });
    }

    /**
     *
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qco___status');
    }
};
