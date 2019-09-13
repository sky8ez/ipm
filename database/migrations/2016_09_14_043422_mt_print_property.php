<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MtPrintProperty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mt_print_property', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('print_id')->index();
          $table->integer('print_detail_id')->index();
          $table->string('category');
          $table->string('name');
          $table->string('value');
          $table->boolean('delete_flag')->default(false);
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
        Schema::drop('mt_print_property');
    }
}
