<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_password_category extends Model
{
  protected $table = 'mt_password_category';

  protected $fillable = [
      'name', 'remarks'
  ];
}
