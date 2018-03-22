<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class SmsRecord extends Model
{
    protected $fillable = [
        'status', 'from', 'to','body','code'
    ];
}
