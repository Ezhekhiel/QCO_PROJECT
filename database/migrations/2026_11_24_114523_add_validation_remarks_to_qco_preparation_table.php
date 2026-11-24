<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qco___preparation', function (Blueprint $table) {
            $table->text('validation_remarks')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('qco___preparation', function (Blueprint $table) {
            $table->dropColumn('validation_remarks');
        });
    }
};