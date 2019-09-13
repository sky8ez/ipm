<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tr_project extends Model
{
  protected $table = 'tr_project';

  protected $fillable = [
      'project_date', 'item_id', 'qty',
      'price','total','remark'
  ];
}
