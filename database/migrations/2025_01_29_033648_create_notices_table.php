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
        Schema::create('notices', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->integer('order')->default(0); // Numeric 'order' column with a default value of 0
            $table->string('title'); // Title column
            $table->longText('description'); // Description column
            $table->date('start_date'); // Start Date
            $table->date('end_date'); // End Date
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
