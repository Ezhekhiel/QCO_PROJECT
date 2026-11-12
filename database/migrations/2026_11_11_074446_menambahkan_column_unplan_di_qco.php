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
            $table->string('status_plan', 50)->default('plan')->after('status');
            $table->unsignedBigInteger('parent_id')->nullable()->after('status_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qco', function (Blueprint $table) {
            // $table->dropForeign(['parent_id']); // Uncomment jika pakai foreign key di atas
            $table->dropColumn(['status_plan', 'parent_id']);
        });
    }
};
