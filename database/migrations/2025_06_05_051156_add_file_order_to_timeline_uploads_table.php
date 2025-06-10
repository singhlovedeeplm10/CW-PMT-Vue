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
    Schema::table('timeline_uploads', function (Blueprint $table) {
        $table->integer('file_order')->nullable()->after('file_link');
    });
}

    /**
     * Reverse the migrations.
     */
   public function down()
{
    Schema::table('timeline_uploads', function (Blueprint $table) {
        $table->dropColumn('file_order');
    });
}
};
