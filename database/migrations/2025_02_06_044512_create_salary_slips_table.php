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
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('employee_name');
            $table->string('month_year');
            $table->integer('total_salary');
            $table->integer('basic_salary')->nullable();
            $table->integer('house_rent')->nullable();
            $table->integer('conveyance')->nullable();
            $table->integer('telephone')->nullable();
            $table->integer('medical')->nullable();
            $table->integer('other')->nullable();
            $table->integer('tax')->nullable();
            $table->integer('unpaid_leaves_deduction')->nullable();
            $table->integer('provident_fund')->nullable();
            $table->integer('employee_state_insurance')->nullable();
            $table->integer('total_deductions')->nullable();
            $table->integer('extra_working_incentive')->nullable();
            $table->integer('gratuity')->nullable();
            $table->integer('bonus')->nullable();
            $table->integer('total_incentives')->nullable();
            $table->integer('total_leaves')->nullable();
            $table->integer('extra_working_days')->nullable();
            $table->integer('earned_leave')->nullable();
            $table->integer('leaves_without_pay')->nullable();
            $table->integer('net_salary_credited')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
    }
};
