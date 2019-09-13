<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mt_password_word extends Model
{
  protected $table = 'mt_password_word';

  protected $fillable = [
      'type', 'word'
  ];
}
