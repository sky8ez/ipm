<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MtUserAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mt_user_access', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name',20);
          $table->string('role'); // user, admin
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
        Schema::drop('mt_user_access');
    }
}
