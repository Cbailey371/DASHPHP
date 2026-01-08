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
        Schema::create('sql_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('sql_query'); // WARNING: Raw SQL
            $table->json('chart_config')->nullable(); // Config for Chart.js
            $table->boolean('is_public')->default(false); // Visible to other roles
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Creator
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sql_reports');
    }
};
