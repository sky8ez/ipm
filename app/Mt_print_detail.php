<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_print_detail extends Model
{
  protected $table = 'mt_print_detail';

  protected $fillable = [
      'print_id', 'kind','sequence_no','type','value','value_type','value_format'
  ];
}
