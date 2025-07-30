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
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('trainee_name');
            $table->string('trainee_email')->unique();
            $table->date('trainee_DOB')->nullable(); 
            $table->string('trainee_qualifications')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable(); 
            $table->string('trainee_contact', 10)->nullable();
            $table->date('training_start_date')->nullable();
            $table->date('training_end_date')->nullable();
            $table->text('training_note')->nullable();
            $table->enum('status', ['active', 'completed', 'not completed'])->default('active');
            $table->string('trainee_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};
