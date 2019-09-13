<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrAudit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_audit', function (Blueprint $table) {
          $table->increments('id');
          $table->string('transaction_category');
          $table->integer('transaction_id')->index();
          $table->string('status'); //insert,edit,delete,print
          $table->string('column'); // column / printer name untuk print
          $table->string('value_old'); //
          $table->string('value_new'); //
          $table->integer('modified_user_id'); //perubah
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
        Schema::drop('tr_audit');
    }
}
