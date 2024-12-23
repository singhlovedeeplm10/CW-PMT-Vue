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
        Schema::create('likes_comments', function (Blueprint $table) {
            $table->id();
            $table->json('timeline_id');
            $table->json('timeline_uploads_id');
            $table->integer('likes')->default(0);  // Change from json to integer
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('user_id'); // New user_id column
            $table->timestamps();
        
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_likes_comments');
    }
};
