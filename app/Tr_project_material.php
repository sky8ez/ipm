<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tr_project_material extends Model
{
  protected $table = 'tr_project_material';

  protected $fillable = [
      'project_id', 'material_id', 'qty','price','total'
  ];
}
