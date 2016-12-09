<?php
namespace App\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Drivers;
use JWTAuth;
use App\OrderRecord;
use App\Http\Requests;
use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
class OrderController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }

  /**
   *@author Arius
   *@function get all record for a user by token
   *
   *@todo model not exit
   *@return success or not
   */
    public function addRecord(Request $request)
    {
      $user = JWTAuth::touser();
      $detail = Redis::hgetall('usecar:'.$user['tel']);
      //To get the drive's position AND driverPhone from redis;
      $driverPhone = $detail['driverPhone'];
      $driverPosition = Redis::hget('driver:'.$detail['driverPhone'], 'driverPosition');

      if ($detail) {
        $input['userphone']=$user['tel'];
        $input['driverPhone']=$driverPhone;
        $input['orderNum']=time().rand(10,100);
        $input['from']=$detail['from'];
        $input['price']=$detail['price'];
        $input['discount']=0;
        $input['time']=date('y-m-d h:i:s',time());
        $input['passengerNum']=$detail['passengerNum'];
        $input['destination']=$detail['destination'];
        $result=OrderRecord::create($input);
      }
      else {
        $result = $this->returnMsg('500','ERROR REQUEST');
        return response()->json($result);
      }
      if ($result) {
        $result = $this->returnMsg('200','SAVED',['orderNum'=>$input['orderNum']]);
        Redis::del('usecar:'.$request->get('tel'));
        return response()->json($result);
      }
      else {
        $result = $this->returnMsg('500','ERROR ARRAY');
        return response()->json(compact($result));
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
        $user = JWTAuth::touser();
        $existname = Redis::EXISTS('usecar:'.$user['tel']);
        if ($existname) {
          $name = Redis::hset('usecar:'.$user['tel'],'isAccept',0);
          $name = Redis::hset('usecar:'.$user['tel'],'driverPhone',null);
          $result = $this->returnMsg('200','ok');
          return response()->json($result);
        }
        else {
          $result = $this->returnMsg('500','order Not Found');
          return response()->json($result);
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

      $ownertel = JWTAuth::toUser();

      $old = OrderRecord::where('orderNum','=',$request->input('num'));
      $last = OrderRecord::where('orderNum','=',$request->input('num'))->first();
      if ($last) {
        if ($ownertel['tel']!=$last['userphone']) {
          $result = $this->returnMsg('500','NO PERMISSION');
          return response()->json($result);
        }
        $last = json_decode($last,true);
        $last['comment']=$request->input('star');
        $result=$old->update($last);

        $driverStar = DB::table('drivers')
            ->where('driverPhone',$last['driverPhone'])
            ->value('stars');
        //update the driver 's stars
        DB::table('drivers')
            ->where('driverPhone',$last['driverPhone'])
            ->update(['stars' => ($driverStar+$last['comment'])/2]);
        if ($result) {
          $result = $this->returnMsg('200','ok');
          return response()->json($result);
        }
        else {
          $result = $this->returnMsg('500','fail update');
          return response()->json($result);
        }
      }
      else {
          $result = $this->returnMsg('500','error orderNum');
          return response()->json($result);
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
      $result = $this->returnMsg('500','error number');
      return response()->json($result);
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
      $one=OrderRecord::where("orderNum","=",$request->get('num'))->first();
      $ownertel = JWTAuth::toUser();
      $ownertel = $ownertel['tel'];
      if ($one) {
        if ($ownertel!=$one['userphone']) {
          $result = $this->returnMsg('500','NO permission');
          return response()->json($result);
        }
      }else{
        $result = $this->returnMsg('500','error id');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$one);
      return response()->json($result);
    }

}
