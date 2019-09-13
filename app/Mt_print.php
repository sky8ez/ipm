<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_print extends Model
{
  protected $table = 'mt_print';

  protected $fillable = [
      'name', 'category','paper_size','paper_orientation',
      'margin_top','margin_left','margin_bottom','margin_right',
      'header_height','row_height','table_top','table_row_count',
      'table_border_style','font_family','font_size','default_flag',
      'active_flag','header_flag','footer_flag','first_header_flag',
      'last_footer_flag','header_query','detail_query','has_detail'
  ];
}
