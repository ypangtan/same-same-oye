<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRadioListenerSnapshotsTable extends Migration
{
    /**
     * Periodic snapshots of Icecast's live listener count (Icecast itself only exposes the
     * current number, not history — this table is what the "listeners over time" graph reads).
     * Written by the radio:snapshot-listeners scheduled command.
     */
    public function up()
    {
        Schema::create('radio_listener_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('listeners')->default(0);
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('radio_listener_snapshots');
    }
}
