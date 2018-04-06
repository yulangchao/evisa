<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class UserBaseInfo extends Model
{
    protected $fillable = [
        'user_id', 'birthday'
      ];
}
