<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_id')->unique();
            $table->string('original_transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('product_id')->nullable();
            $table->tinyInteger( 'platform' )->default(1);
            $table->text('receipt_data')->nullable();
            $table->text('signature')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_response')->nullable();
            $table->string('event_type')->nullable();
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('original_transaction_id');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}
