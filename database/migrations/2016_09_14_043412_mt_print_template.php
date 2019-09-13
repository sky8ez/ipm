<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MtPrintTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mt_print', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name');
          $table->string('category'); //filing, handover
          $table->string('paper_size');
          $table->string('paper_orientation');
          $table->string('margin_top');
          $table->string('margin_left');
          $table->string('margin_bottom');
          $table->string('margin_right');
          $table->string('header_height'); //untuk tinggi column header
          $table->string('row_height'); //untuk tinggi row
          $table->string('table_top'); //untuk posisi table
          $table->string('table_row_count'); //untuk posisi table
          $table->string('table_border_style'); //untuk posisi table
          $table->string('font_family');
          $table->string('font_size');
          $table->boolean('default_flag');
          $table->boolean('active_flag');
          $table->boolean('header_flag');
          $table->boolean('footer_flag');
          $table->boolean('first_header_flag');
          $table->boolean('last_footer_flag');
          $table->text('header_query');
          $table->text('detail_query');
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
        Schema::drop('mt_print');
    }
}
