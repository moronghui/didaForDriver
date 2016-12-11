<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
  protected $fillable = array('id','driverPhone','password','name','idNum','bankCard','head','motoNum','motoType','license','idFrontPhoto','idBehindPhoto','motoFrontPhoto','motoSidePhoto','isPass','regTime','orderFnishedNum','stars');
    //获取该司机车的车型
    public function getType($driverPhone)
    {
    	$driverMes=self::where('driverPhone','=',$driverPhone)->get();
        foreach ($driverMes as $driver) {
            return $driver->motoType;
        }
    }
}
