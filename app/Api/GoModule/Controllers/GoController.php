<?php
namespace App\Api\GoModule\Controllers;

use App\Api\Controllers\BaseController;
use App\Http\Requests\checkDriverLocation;
use App\Wechat;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Redis;

class GoController extends BaseController
{
    /**
     * The authentication guard that should be used.
     *
     * @var string
     */
    public function __construct()
    {
        parent::__construct();

    }


    /**
     *<author johannlai@outlook.com>
     *@ function to check if the order is accept
     *@ params token
     *@ return if accepted return the the driver's infomation
     *        else return not be accepted
     */
    //Driver check if his location has orders
    public function userCheckOrder(Request $request)
    {
        //get Token
        $token = JWTAuth::getToken();

        //get the user infomation
        $user_json = JWTAuth::toUser($token);

        $user = json_decode($user_json, true);
        //test phone
        $user['userphone'] = '15521145890';

        $hashName = 'order:'.$user['userphone'];
        $orderInfo = Redis::HGetAll($hashName);

        //if cannot find the oder return not found
        if (empty($orderInfo)) {
            $result = $this->result('204', 'ORDER_NOT_FOUND');
            return response()->json($result);
        } else if ($orderInfo['isAccept']==1) {
            $result = $this->result('200', 'OK', $orderInfo);
            return response()->json($result);
        } else {
            $result = $this->result('204', 'NOT_ACCEPTED');
            $driverPhone = $user['driverPhone'];
            $driver = $this->getDriverInfo($driverPhone);
            $result = array_merge($result,$driver);
            return response()->json($result);
        }
    }

    /*
     *@author johannlai
     *@function to get the driver's Location
     *@params driver's phone
     *
     */
    public function checkDriverLocation(Request $request)
    {
        return 'ok';
    }


    /**
     *@function to get the driver through phoneNumber
     *
     *@todo get the driver from the database's table
     */
    private function getDriverInfo($driverPhone){
    /*
        $driver = DB::table('driver')
            ->where('phone', $driverPhone)
            ->value('head', 'name', 'star', 'orderNum', 'carnumber')->first();
        $driver['driverLocation'] = Redis::hGetAll('driver:'.$driverPhone);
     */
    }


    /*
     *@funtion : to build the reponse infomation
     *
     */
    public function result ( $code=200, $message="ok", $data=null )
    {
        $result['code']= $code;
        $result['message']= $message;
        $result['data']= $data;
        return $result;
    }
}
