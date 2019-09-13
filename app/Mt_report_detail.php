<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_report_detail extends Model
{
  protected $table = 'mt_report_detail';

  protected $fillable = [
      'report_id', 'column_name','alignment','font_color',
      'font_size','type','format'
  ];
}
