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
            $table->string('address')->nullable();
            $table->string('qualifications')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('academic_documents')->nullable();
            $table->string('identification_documents')->nullable();
            $table->string('offer_letter')->nullable();
            $table->string('joining_letter')->nullable();
            $table->string('contract')->nullable();
            $table->string('user_image')->nullable(); // New column for user image
            $table->date('user_DOB')->nullable(); // New column for user date of birth
            $table->timestamps();
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
