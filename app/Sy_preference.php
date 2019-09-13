<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sy_preference extends Model
{
  protected $table = 'sy_preference';

  protected $fillable = [
      'category', 'name', 'value'
  ];
}
