<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRadioSettingsTable extends Migration
{
    /**
     * Single-row settings table for the radio module — currently just the fallback cover image
     * shown (via /api/v1/radio/now-playing) for tracks that were uploaded without their own.
     */
    public function up()
    {
        Schema::create('radio_settings', function (Blueprint $table) {
            $table->id();
            $table->string('default_image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('radio_settings');
    }
}
