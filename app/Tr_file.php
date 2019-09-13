<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tr_file extends Model
{
  protected $table = 'tr_file';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['category','file_name','parent_category','parent_id','path','name','type','size','ext'];

}
