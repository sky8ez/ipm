<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tr_project_expense extends Model
{
  protected $table = 'tr_project_expense';

  protected $fillable = [
      'project_id', 'name', 'amount',
      'remark'
  ];
}
