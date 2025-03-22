<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnoucementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onUpdate( 'restrict')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('promo_code')->nullable();
            $table->string('image')->nullable();
            $table->string('unclaimed_image')->nullable();
            $table->string('claiming_image')->nullable();
            $table->string('claimed_image')->nullable();
            $table->tinyInteger('view_once')->nullable()->default(1);
            $table->tinyInteger('new_user_only')->nullable()->default(1);
            $table->decimal('min_spend',16,2)->nullable();
            $table->integer('min_order')->nullable()->default(0);
            $table->tinyInteger('discount_type')->default(1);
            $table->decimal('discount_amount',16,2)->nullable();
            $table->integer('expired_in')->nullable();
            $table->tinyInteger('status')->default(10);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('expired_date')->nullable();
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
        Schema::dropIfExists('announcements');
    }
}
