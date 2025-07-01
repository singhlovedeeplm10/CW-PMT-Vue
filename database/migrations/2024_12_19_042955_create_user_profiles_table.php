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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_personal_email')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('qualifications')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('academic_documents')->nullable();
            $table->string('identification_documents')->nullable();
            $table->string('offer_letter')->nullable();
            $table->string('joining_letter')->nullable();
            $table->string('contract')->nullable();
            $table->string('user_image')->nullable(); 
            $table->date('user_DOB')->nullable(); 
            $table->enum('gender', ['male', 'female'])->nullable(); 
            $table->string('contact', 10)->nullable(); 
            $table->timestamps();
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_releaving')->nullable();
            $table->text('releaving_note')->nullable();
            $table->string('next_appraisal_month')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('temporary_address')->nullable();
            $table->string('alternate_contact_number', 15)->nullable();
            $table->string('designation')->nullable();
            $table->string('current_salary')->nullable();
            $table->json('appraisals')->nullable();
            $table->json('credentials')->nullable();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
