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
        Schema::create('timeline_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timeline_id');
            $table->string('file_path')->nullable(); // Made nullable
            $table->enum('file_type', ['image', 'video'])->nullable(); // Made nullable
            $table->string('file_link')->nullable();
            $table->integer('file_order')->nullable();
            $table->timestamps();
        
            $table->foreign('timeline_id')->references('id')->on('timelines')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_uploads');
    }
};
