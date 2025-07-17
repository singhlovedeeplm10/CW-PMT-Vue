<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device');
            $table->string('device_number');
            $table->text('note')->nullable();
            $table->text('description')->nullable();
            $table->json('developer_assign_list')->nullable();
            $table->date('date_of_assign')->nullable();
            $table->enum('status', ['0', '1'])->nullable();
            $table->json('history')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
