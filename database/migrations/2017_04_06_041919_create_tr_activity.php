<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tr_activity_log', function (Blueprint $table) {
          $table->increments('id');
          $table->datetime('activity_date');
          $table->string('transaction_category');
          $table->integer('transaction_id')->index();
          $table->integer('user_id')->index();
          $table->string('action');
          $table->string('ip');
          $table->string('browser');
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
        Schema::drop('tr_activity_log');
    }
}
