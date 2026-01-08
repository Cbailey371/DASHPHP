<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('custom_widgets', function (Blueprint $table) {
            $table->string('section')->default('logistica')->after('title'); // logistica, ventas, general
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_widgets', function (Blueprint $table) {
            $table->dropColumn('section');
        });
    }
};
