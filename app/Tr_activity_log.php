<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tr_activity_log extends Model
{
  protected $table = 'tr_activity_log';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['activity_date', 'transaction_category','transaction_id', 'user_id','action', 'ip', 'browser'];

}
