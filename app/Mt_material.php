<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_material extends Model
{
  protected $table = 'mt_material';

  protected $fillable = [
      'material_code', 'material_name', 'type',
      'unit','length','width','file_id','price'
  ];
}
