<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_pattern extends Model
{
  protected $table = 'mt_pattern';

  protected $fillable = [
      'pattern_name'
  ];
}
