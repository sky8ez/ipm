<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePasswordCategoryUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mt_password_category_user', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('user_id')->index();
          $table->integer('password_category_id')->index(); //number, word
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
      Schema::drop('mt_password_category_user');
    }
}
