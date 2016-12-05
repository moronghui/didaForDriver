<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Drivers;
use JWTAuth;
use App\OrderRecord;
use App\Http\Requests;

class OrderController extends BaseController
{
  /**
   *@author Arius
   *@function get all record for a user by token
   *
   *@todo model not exit
   *@return record json
   */
    public function orderRecordList()
    {
      $order=JWTAuth::toUser();
      $orderTel=$order['tel'];
      $list=OrderRecord::where("userphone","=",$orderTel)->first();
      if ($list) {
        return $list;
      }
      $arr = array ('status'=>"ERROR NUMBER");
      return response()->json(compact('arr'));
    }
    /**
     *@author Arius
     *@function get an information by id
     *
     *@todo model not exit
     *@return record json
     */
    public function orderRecordOne($id)
    {
      $one=OrderRecord::where("orderNum","=",$id)->first();
      if ($one) {
        return $one;
      }
      $arr = array ('status'=>"ERROR ID");
      return response()->json(compact('arr'));
    }

}
