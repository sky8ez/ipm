<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tr_file', function (Blueprint $table) {
          $table->increments('id');
          $table->string('file_name');
          $table->string('parent_category');
          $table->integer('parent_id')->index();
          $table->string('type'); //image,file,
          $table->string('path');
          $table->string('name')->unique();
          $table->string('ext');
          $table->string('size');
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
      Schema::drop('tr_file');
    }
}
