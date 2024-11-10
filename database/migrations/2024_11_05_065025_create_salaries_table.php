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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->date('date');
            $table->decimal('amount_credited', 8, 2);
            $table->decimal('amount_deducted', 8, 2)->nullable();
            $table->enum('deduction_type', ['Leaves', 'tax', 'credit'])->nullable();
            $table->integer('unpaid_leaves')->nullable();
            $table->decimal('extra_amount_credited', 8, 2)->nullable();
            $table->enum('credit_type', ['Bonus', 'Incentive', 'OT'])->nullable();
            $table->integer('ot_hours')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
