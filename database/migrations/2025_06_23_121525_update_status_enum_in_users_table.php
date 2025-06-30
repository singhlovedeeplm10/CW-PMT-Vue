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
    DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('0', '1', '2') DEFAULT '1'");
}

    /**
     * Reverse the migrations.
     */
   public function down()
{
    DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('0', '1') DEFAULT '1'");
}

};
