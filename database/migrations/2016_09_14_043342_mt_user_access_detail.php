<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MtUserAccessDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mt_user_access_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_access_id')->index();
            $table->string('module_id');
            $table->string('module_name');
            $table->string('condition');
            $table->boolean('cond_flag');
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
        Schema::drop('mt_user_access_detail');
    }
}
