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
        Schema::create('daily_tasks', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID column
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade'); // Foreign key to attendance table
            $table->unsignedBigInteger('project_id');
            $table->decimal('hours', 8, 2); // Column to store hours worked (2 decimals)
            $table->text('task_description'); // Column to store task description
            $table->enum('task_status', ['pending', 'in_progress', 'completed']); // Column to store task status
            $table->timestamps(); // Created at and updated at timestamps

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_tasks');
    }
};
