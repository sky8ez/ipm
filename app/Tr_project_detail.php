<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tr_project_detail extends Model
{
  protected $table = 'tr_project_detail';

  protected $fillable = [
      'project_id', 'material_id', 'pattern_id',
      'type','qty','qty_total','price','total','remark'
  ];
}
