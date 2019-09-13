<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_password_list extends Model
{
  protected $table = 'mt_password_list';

  protected $fillable = [
      'name', 'username', 'pass','password_category_id',
      'remarks'
  ];
}
