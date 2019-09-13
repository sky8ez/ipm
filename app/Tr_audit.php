<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tr_audit extends Model
{
  protected $table = 'tr_audit';

   /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['transaction_category','transaction_id',
                          'status','column','value_old','value_new','modified_user_id'];
}
