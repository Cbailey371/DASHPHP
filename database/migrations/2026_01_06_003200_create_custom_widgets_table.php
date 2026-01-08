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
        Schema::create('custom_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('stat'); // stat, chart_line, chart_bar, chart_pie
            $table->string('model'); // Quote, WorkOrder, etc.
            $table->string('aggregate_function')->default('count'); // count, sum, avg, min, max
            $table->string('aggregate_column')->nullable(); // Column to aggregate (e.g. total_amount)
            $table->string('date_column')->default('created_at'); // Column for date grouping
            $table->string('filter_status')->nullable(); // Optional status filter
            $table->string('color')->default('primary'); // success, danger, warning, info
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_widgets');
    }
};
