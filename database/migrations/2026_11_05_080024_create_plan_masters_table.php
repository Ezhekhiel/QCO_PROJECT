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
        Schema::create('plan_masters', function (Blueprint $table) {
            $table->id();
            $table->string('cell', 50)->index();
            $table->string('model', 100);
            $table->string('style_no', 100);
            $table->integer('qty')->default(0);
            $table->date('assembly_start_date')->nullable(); // Tanggal Start Assembly
            $table->date('stitching_start_date')->nullable(); // Tanggal Start Stitching/Upper
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_masters');
    }
};
