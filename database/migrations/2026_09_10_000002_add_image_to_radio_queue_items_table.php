<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToRadioQueueItemsTable extends Migration
{
    public function up()
    {
        Schema::table('radio_queue_items', function (Blueprint $table) {
            $table->string('image')->nullable()->after('file_name');
        });
    }

    public function down()
    {
        Schema::table('radio_queue_items', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
}
