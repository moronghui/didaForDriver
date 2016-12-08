<?php

namespace App\Api\GoModule\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Requests;
use App\Http\Requests\PostRequest;

class UserPostController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    /**
    *文件名(UserPostController.php)
    *
    *根据乘客当前经纬度返回附近可用车的数量
    *
    *@param $request
    */

    public function carNum(Request $request)
    {
        //获得乘客经纬度
        $position = $request->get('position');
        //以逗号拆分经纬度，存为一个数组，0为经度，1为纬度
        $p_data = explode(",", $position);
        //查询司机手机号码，作为查询司机位置的遍历依据
        $drivers = Redis::sinter('driverphone');
        $num = 0;//车辆统计个数num
        foreach ($drivers as $driver) {
            //
            $d_position = Redis::hget('driver:'.$driver, 'position');
            $d_data = explode(",", $d_position);
            $betweenX = $p_data['0']-$d_data['0'];
            $betweenY = $p_data['1']-$d_data['1'];

            /***满足经纬度加减0.02(实际距离为4.4公里)的范围的车辆则算入统计***/
            if (abs($betweenX)<=0.02&&abs($betweenY)<=0.02) {
                $num = $num+1;
            }
        }
        $result = $this->returnMsg('200','success',['carNum'=>$num]);
        return response()->json($result);;
    }

    /**
    *根据乘客端输入的乘客人数、路程距离、车型估价(目前只根据路程，5公里以内5元，以上每0.1公里加0.05元)
    *
    *@param $request
    */

    public function price(Request $request)
    {
        $distance = $request->get('distance');
        if ($distance<=5) {
            $result = $this->returnMsg('200','success',['price'=>5]);
            return response()->json($result);
        } else {
            $p = 5+0.5*($distance-5);
            $result = $this->returnMsg('200','success',['price'=>$p]);
            return response()->json($result);
        }
    }

    /**
    *乘客确认用车后，保存订单信息至服务器，用作发布给司机端
    *
    *用车表名称格式
    *
    *@param Request $request
    */

    public function orderSave(PostRequest $request)
    {

        //获得用户手机号码
        $userphone = $request->get('userphone');
        //存入userphone集合
        $userphone_save = Redis::sadd('userphone', $userphone);
        // dd($userphone);
        //获取集合userphone里的全部手机号码
         // $value = Redis::command('sinter', ['userphone']);
         // dd($value);
        //获得出发地名称
        $from = $request->get('from');
        //获得出发地经纬度
        $fromPosition = $request->get('fromPosition');
        //获得目的地名称
        $destination = $request->get('destination');
        //获得目的地经纬度
        $destinationPositon = $request->get('destinationPositon');
        //获得乘客人数
        $passengerNum = $request->get('passengerNum');
        //获得车类型
        $motoType = $request->get('motoType');

        $isAccept = 0;

        $query = Redis::hmset(
            'usecar:'.$userphone,
            'from',
            $from,
            'fromPosition',
            $fromPosition,
            'destination',
            $destination,
            'destinationPositon',
            $destinationPositon,
            'passengerNum',
            $passengerNum,
            'motoType',
            $motoType,
            'isAccept',
            $isAccept

        );
        if ($query) {
            $result = $this->returnMsg('200','ok');
            return response()->json($result);
        } else {
            $result = $this->returnMsg('500','save order fail');
            return response()->json($result);
        }
    }
}
