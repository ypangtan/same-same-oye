<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteBannerClicksTable extends Migration
{
    public function up()
    {
        Schema::create('website_banner_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_banner_id')->nullable()->constrained('website_banners')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('website_banner_clicks');
    }
}
