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
<<<<<<< HEAD
        if (Schema::connection('erp_db')->getDriverName() !== 'sqlite') {
            return;
        }

=======
>>>>>>> 0d029b8af6345cef325ffdcecdcf9cbf8857bc73
        Schema::connection('erp_db')->create('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('Empresa');
            $table->string('Email')->nullable();
            $table->string('Contact')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('erp_db')->dropIfExists('customers');
    }
};
