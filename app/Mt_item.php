<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_item extends Model
{
  protected $table = 'mt_item';

  protected $fillable = [
      'item_code', 'item_name', 'file_id',
      'remark'
  ];
}
