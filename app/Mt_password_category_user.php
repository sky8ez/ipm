<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_password_category_user extends Model
{
  protected $table = 'mt_password_category_user';

  protected $fillable = [
      'user_id', 'password_category_id'
  ];
}
