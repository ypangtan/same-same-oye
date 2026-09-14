<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRadioListenerSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('radio_listener_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('source_key', 64);
            $table->string('client_id', 32);
            $table->string('ip', 45);
            $table->timestamp('connected_at');
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            // Detection time, not an exact network disconnect timestamp.
            $table->timestamp('disconnected_at')->nullable();
            $table->index(['source_key', 'disconnected_at']);
            $table->index(['source_key', 'last_seen_at', 'ip'], 'radio_listener_daily_index');
        });
        Schema::create('radio_listener_syncs', function (Blueprint $table) {
            $table->string('source_key', 64)->primary();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('failed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('radio_listener_syncs');
        Schema::dropIfExists('radio_listener_sessions');
    }
}
