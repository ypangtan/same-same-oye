<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->onUpdate( 'restrict')->onDelete('cascade');
            $table->timestamp( 'start_date' )->nullable();
            $table->timestamp( 'end_date' )->nullable();
            $table->timestamp( 'cancelled_at' )->nullable();
            $table->tinyInteger( 'platform' )->default(1)->nullable();
            $table->string( 'platform_transaction_id' )->unique()->nullable();
            $table->text( 'platform_receipt' )->nullable();
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
}
