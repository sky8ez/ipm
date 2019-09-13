<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertPrintDetailColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('mt_print_detail', function ($table) {
            $table->string('value_type')->index()->after('value')->default('text');
            $table->string('value_format')->index()->after('value')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('mt_print_detail', function($table) {
          $table->dropColumn('value_type');
          $table->dropColumn('value_format');
       });
    }
}
