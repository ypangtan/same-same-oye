<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRadioQueueItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('radio_queue_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // R2 object key. Kept while queued/reserved, wiped once played (status 30) to free up storage.
            $table->string('file')->nullable();
            // Original uploaded file name. Kept forever so played history stays readable after the file is gone.
            $table->string('file_name')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('position')->default(0);
            // 10 = queued, 20 = reserved/now playing, 30 = played (file deleted, kept as history record)
            $table->tinyInteger('status')->default(10);
            $table->unsignedBigInteger('add_by')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('played_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('radio_queue_items');
    }
}
