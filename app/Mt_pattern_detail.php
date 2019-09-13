<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_pattern_detail extends Model
{
  protected $table = 'mt_pattern_detail';

  protected $fillable = [
      'pattern_id', 'file_id', 'qty',
      'length','width'
  ];
}
