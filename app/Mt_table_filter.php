<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_table_filter extends Model
{
  protected $table = 'mt_table_filter';

  protected $fillable = [
      'user_id', 'category', 'form_id','alias',
      'column_table','column_type','column_name',
      'filter','value'
  ];
}
