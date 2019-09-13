<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_user_access_detail extends Model
{
  protected $table = 'mt_user_access_detail';

  protected $fillable = [
      'user_access_id', 'module_id',
      'module_name','condition','cond_flag'
  ];
}
