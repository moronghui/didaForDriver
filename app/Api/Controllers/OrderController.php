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
   *@return success or not
   */
    public function addRecord(Request $request)
    {
      $detail = Redis::hgetall('usecar:'.$request->get('tel'));
      if ($detail) {
        $input['userphone']=$request->get('tel');
        $input['driverPhone']='123';
        $input['orderNum']=time().rand(10,100);
        $input['from']=$detail['from'];
        $input['price']='10';
        $input['discount']=0;
        $input['time']=date('y-m-d h:i:s',time());
        $input['passengerNum']=$detail['passengerNum'];
        $input['destination']=$detail['destination'];
        $result=OrderRecord::create($input);
      }
      else {
        $arr = array ('status'=>"ERROR REQUEST");
        return response()->json(compact('arr'));
      }
      if ($result) {
        $arr = array ('status'=>"SAVED","orderNum"=>$input['orderNum']);
        Redis::del('usecar:'.$request->get('tel'));
        return response()->json(compact('arr'));
      }
      else {
        $arr = array ('status'=>"ERROR ARRAY");
        return response()->json(compact('arr'));
      }
    }
    /**
     *@author Arius
     *@function drivers refuse the order after accpeted
     *
     *
     *@return success or not
     */
      public function orderBack(Request $request)
      {
        $name = Redis::EXISTS('usecar:'.$request->post('tel'));
        if ($name) {
          $name = Redis::hset('usecar:'.'123','isAccept',0);
          $name = Redis::hset('usecar:'.'123','driverphone',null);
          $arr = array ('status'=>"SUCCESSED");
          return response()->json(compact('arr'));
        }
        else {
          $arr = array ('status'=>"ERROR NUMBER");
          return response()->json(compact('arr'));
        }
      }
  /**
   *@author Arius
   *@function set star for order service
   *
   *@return success or not
   */
    public function orderStar(Request $request)
    {
      $old = OrderRecord::where('orderNum','=',$request->input('num'));
      $last = OrderRecord::where('orderNum','=',$request->input('num'))->first();
      $ownertel = JWTAuth::toUser();
      $ownertel = $ownertel['tel'];
      if ($last) {
        if ($ownertel!=$last['userphone']) {
          $arr = array ('status'=>"NO PERMISSON");
          return response()->json(compact('arr'));
        }
        $last = json_decode($last,true);
        $last['comment']=$request->input('star');
        $result=$old->update($last);
        if ($result) {
          $arr = array ('status'=>"SUCCESSED");
          return response()->json(compact('arr'));
        }
        else {
          $arr = array ('status'=>"FAILED UPDATE");
          return response()->json(compact('arr'));
        }
      }
      else {
        $arr = array ('status'=>"ERROR ORDERNUM");
        return response()->json(compact('arr'));
      }
    }
  /**
   *@author Arius
   *@function get all record for a user by token
   *
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
     *
     *@return record json
     */
    public function orderRecordOne(Request $request)
    {
      $one=OrderRecord::where("orderNum","=",$request->get('id'))->first();
      $ownertel = JWTAuth::toUser();
      $ownertel = $ownertel['tel'];
      if ($one) {
        if ($ownertel!=$one['userphone']) {
          $arr = array ('status'=>"NO PERMISSON");
          return response()->json(compact('arr'));
        }
      }
      $arr = array ('status'=>"ERROR ID");
      return response()->json(compact('arr'));
    }

}
