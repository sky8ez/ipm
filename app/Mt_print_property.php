<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_print_property extends Model
{
  protected $table = 'mt_print_property';

  protected $fillable = [
      'print_id', 'print_detail_id','category','name','value'
  ];
}
