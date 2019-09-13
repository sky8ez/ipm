<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProjectMaterial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tr_project_material', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('project_id')->index();
        $table->integer('material_id')->index();
        $table->double('qty');
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
        Schema::drop('tr_project_material');
    }
}
