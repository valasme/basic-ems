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
        Schema::create('due_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->date('pay_date');
            $table->timestamps();

            $table->index(['user_id', 'status', 'pay_date']);
            $table->index(['employee_id', 'pay_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('due_payments');
    }
};
