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
        Schema::create('project_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('billing_type', ['Online', 'Offline', 'Fixed'])->nullable();
            $table->integer('billing_hours')->nullable();
            $table->json('milestone_ids')->nullable(); // Change to JSON
            $table->unsignedBigInteger('billing_credential_id')->nullable();
            $table->string('credentials')->nullable();
            $table->string('technology_front')->nullable();
            $table->string('technology_back')->nullable();
            $table->string('technology_dbms')->nullable();
            $table->string('dependencies')->nullable();
            $table->json('upload_ids')->nullable(); // Change to JSON
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('billing_credential_id')->references('id')->on('billing_credentials')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_details');
    }
};
