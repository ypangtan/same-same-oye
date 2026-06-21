<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePopAnnouncementClicksTable extends Migration
{
    public function up()
    {
        Schema::create('pop_announcement_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pop_announcement_id')->nullable()->constrained('pop_announcements')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
            $table->timestamps();

            $table->foreign('pop_announcement_id')->references('id')->on('pop_announcements')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pop_announcement_clicks');
    }
}
