<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderRecord extends Model
{
      protected $fillable = array('orderNum','userphone','from',
      'destination',
      'passengerNum',
      'driverPhone',
      'price',
      'discount',
      'time',
      'comment');
}
