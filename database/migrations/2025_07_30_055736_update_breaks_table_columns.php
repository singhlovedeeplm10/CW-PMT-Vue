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
         Schema::table('breaks', function (Blueprint $table) {
            // Change start_time from timestamp to datetime
            $table->dateTime('start_time')->change();
            
            // Change end_time from timestamp to datetime
            $table->dateTime('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('breaks', function (Blueprint $table) {
            // Reverse the changes if needed
            $table->timestamp('start_time')->change();
            $table->timestamp('end_time')->nullable()->change();
        });
    }
};
