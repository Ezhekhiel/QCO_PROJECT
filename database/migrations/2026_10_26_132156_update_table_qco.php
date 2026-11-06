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
        Schema::table('qco', function (Blueprint $table) {
            // 1. Tanggal (Boleh kosong / NULL)
            $table->date('completion_date')->nullable()->change();

            // 2. Angka / Integer (Default-nya angka 0)
            $table->integer('completion')->default(0)->change();

            // 3. Teks / Status (Default-nya teks "NOT YET")
            $table->string('status')->default('NOT YET')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qco', function (Blueprint $table) {
            // Kembalikan ke kondisi sebelum file migration ini dibuat:

            // 1. Jika sebelumnya completion_date WAJIB diisi (tidak boleh null):
            $table->date('completion_date')->nullable(false)->change();

            // 2. Jika sebelumnya completion TIDAK PUNYA default 0 (atau default-nya kosong/null):
            $table->integer('completion')->default(null)->change();
            // Atau jika sebelumnya tidak ada default sama sekali (sesuaikan dengan kondisi asli)

            // 3. Jika sebelumnya status TIDAK PUNYA default 'NOT YET':
            $table->string('status')->default(null)->change();
        });
    }
};
