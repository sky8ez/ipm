<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_user_access extends Model
{
  protected $table = 'mt_user_access';

  protected $fillable = [
      'name', 'role'
  ];
}
