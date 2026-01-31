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
        // Solo ejecutar si estamos en modo Mock (SQLite)
        if (Schema::connection('erp_db')->getDriverName() !== 'sqlite') {
            return;
        }
        Schema::connection('erp_db')->create('bills_work_orders', function (Blueprint $table) {
            $table->string('id')->primary(); // WO0001
            $table->string('Invoice'); // Foreign key to quotes.id
            $table->date('Date')->nullable();
            // Add other fields if necessary
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('erp_db')->dropIfExists('bills_work_orders');
    }
};
