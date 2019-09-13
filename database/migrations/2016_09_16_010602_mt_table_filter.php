<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MtTableFilter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mt_table_filter', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_id')->index();
        $table->string('category'); // filter / sort
        $table->string('form_id');
        $table->string('alias'); //alias column
        $table->string('column_name'); //image,file,
        $table->string('column_type'); //datetime, text,boolean
        $table->string('column_table'); //customer, supplier, ,
        $table->string('filter'); // like, or, and, not like , > , <
        $table->string('value'); // filter value
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
        Schema::drop('mt_table_filter');
    }
}
