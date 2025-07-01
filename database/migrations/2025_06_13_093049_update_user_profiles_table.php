<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->renameColumn('address', 'permanent_address');

            $table->date('date_of_joining')->nullable();
            $table->date('date_of_releaving')->nullable();
            $table->text('releaving_note')->nullable();
            $table->string('next_appraisal_month')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('temporary_address')->nullable();
            $table->string('alternate_contact_number', 15)->nullable();
            $table->string('designation')->nullable();
            $table->string('current_salary')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->renameColumn('permanent_address', 'address');

            // Drop added columns
            $table->dropColumn([
                'date_of_joining',
                'date_of_releaving',
                'releaving_note',
                'next_appraisal_month',
                'blood_group',
                'temporary_address',
                'alternate_contact_number',
                'designation',
                'current_salary',
            ]);
        });
    }
}

