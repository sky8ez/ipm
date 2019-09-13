<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtPasswordList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mt_password_list', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name')->unique(); //number, word
          $table->string('username');
          $table->string('pass');
          $table->string('remarks');
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
       Schema::drop('mt_password_list');
    }
}
