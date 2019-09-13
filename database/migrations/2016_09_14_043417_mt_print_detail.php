<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MtPrintDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mt_print_detail', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('print_id')->index();
          $table->string('kind'); //footer / header / content / detail
          $table->integer('sequence_no'); 
          $table->string('type'); //textbox / label / rectangle
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
        Schema::drop('mt_print_detail');
    }
}
