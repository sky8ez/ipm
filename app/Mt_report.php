<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_report extends Model
{
  protected $table = 'mt_repot';

  protected $fillable = [
      'name', 'title','group_flag','sub_group_flag'
  ];
}
