<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->decimal('payment', 15, 2)->default(0);
            $table->date('due_date');
            $table->integer('payment_terms')->default(30);
            $table->string('billing_cycle')->default('Monthly'); // e.g., Weekly, Monthly, Quarterly, Annually
            $table->integer('payment_method')->default(1); //1: Bank Transfer, 2: Credit Card, 3: Cash, 4: Check, 5: Online Payment
            $table->integer('status')->default(0); //0: Unpaid, 1: Partial, 2: Paid, 3: Canceled
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
