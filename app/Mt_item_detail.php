<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_item_detail extends Model
{
  protected $table = 'mt_item_detail';

  protected $fillable = [
      'type', 'item_id', 'pattern_id','material_id',
      'qty','remark'
  ];
}
