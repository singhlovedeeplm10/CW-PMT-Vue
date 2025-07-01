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
        Schema::create('leaves', function (Blueprint $table) {
           $table->id();
           $table->foreignId('user_id')->constrained()->onDelete('cascade');
           $table->enum('type_of_leave', [ 'Short Leave', 'Half Day Leave', 'Full Day Leave', 'Work From Home Full Day','Work From Home Half Day']);
           $table->enum('half', ['First Half', 'Second Half'])->nullable();
           $table->date('start_date')->nullable();
           $table->date('end_date')->nullable();
           $table->time('start_time')->nullable();
           $table->time('end_time')->nullable();
           $table->text('reason');
           $table->enum('status', ['pending', 'approved', 'disapproved','hold', 'canceled'])->default('pending');
           $table->string('contact_during_leave', 15);
           $table->string('last_updated_by');
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
