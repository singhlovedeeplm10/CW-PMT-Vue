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
    Schema::table('user_profiles', function (Blueprint $table) {
        $table->json('appraisals')->nullable()->after('current_salary');
        $table->json('credentials')->nullable()->after('appraisals');
    });
}

    /**
     * Reverse the migrations.
     */
 public function down()
{
    Schema::table('user_profiles', function (Blueprint $table) {
        $table->dropColumn(['appraisals', 'credentials']);
    });
}
};
