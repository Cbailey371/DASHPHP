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
        // Solo ejecutar si estamos en modo Mock (SQLite)
        if (Schema::connection('erp_db')->getDriverName() !== 'sqlite') {
            return;
        }

=======
>>>>>>> 0d029b8af6345cef325ffdcecdcf9cbf8857bc73
        // Mock schema for ERP Quotes table
        Schema::connection('erp_db')->create('quotes', function (Blueprint $table) {
            $table->string('id')->primary(); // COT00001
            $table->unsignedBigInteger('Cliente'); // ID Cliente
            $table->decimal('Total', 10, 2);
            $table->date('Date');
            $table->string('SalesTerm')->nullable(); // COD, CREDIT
            $table->string('Status'); // APPROVED, PENDING
            // No standard timestamps in legacy ERP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('erp_db')->dropIfExists('quotes');
    }
};
