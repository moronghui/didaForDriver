<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    //获取该司机车的车型
    public function getType($driverPhone)
    {
    	$driverMes=self::where('driverPhone','=',$driverPhone)->get();
        foreach ($driverMes as $driver) {
            return $driver->motoType;
        }
    }
}
