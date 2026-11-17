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
        Schema::table('qco___preparation_files', function (Blueprint $table) {
            $table->string('user_update')->nullable()->after('file'); // Sesuaikan tipe data (string/integer) dengan kolom NIK Anda
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qco___preparation_files', function (Blueprint $table) {
            $table->dropColumn('user_update');
        });
    }
};
