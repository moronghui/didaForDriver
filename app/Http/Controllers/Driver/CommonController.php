<?php

namespace app\Http\Controllers\Driver;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Redis;
use App\Driver;
use App\User;

class CommonController extends Controller
{

    //set driverPhone
    //hash startcar:driverPhone

    /**
    *调用场景：司机端，司机点击开始出车后
    *功能：将出车司机信息上传到服务器，以接收订单的推送
    *请求方式：post
    *@var Redis $redis
    *@var bool $setFlag
    *@var bool $hashFlag
    *@var string $flag
    *@var object driverMes
    *@var string motoType
    *@param string driverPosition
    *@param string driverPhone
    *@return json $array
    */
    public function start(Request $request)
    {
        $driverPosition=$request->input('driverPosition');	//获取司机位置经纬度
        $driverPhone=$request->input('driverPhone'); //获取司机的手机号码
        /*if (is_null($driverPosition)|| is_null($driverPhone) || $driverPosition=='' || $driverPhone=='') {
            $array=[
                'flag'=>'0'
            ];
            return json_encode($array);
        }*/
        $redis=new Redis();	//创建redis实例
        $redis->connect('localhost','6379'); //连接redis服务器
        //保存司机手机号码到set driverPhone
        $setFlag=(bool) $redis->sAdd('driverPhone',$driverPhone);
        //保存司机其它信息到hash startcar:driverphone中
        $driverMes=Driver::where('driverPhone','=',$driverPhone)->get();//查询该司机车的车型保存到redis中
        $motoType=$driverMes[0]->motoType;
        $hashFlag=(bool) $redis->hMset("startcar:".$driverPhone,array('driverPosition'=>$driverPosition,'isFree'=>'1','type'=>$motoType));
        if ($setFlag && $hashFlag) {
            $flag=1;
        } else {
            $flag=0;
        }
        $array=[
            'flag'=>$flag
        ];

        return json_encode($array);
    }

    /**
	*调用场景：司机端，司机点击结束出车后
	*功能：将该司机从实时出车表中去除
	*请求方式：post
	*@var Redis $redis
    *@var bool $setFlag
    *@var bool $hashFlag
    *@var string $flag
    *@param string driverPhone
    *@return json $array
	*/
	public function end(Request $request)
	{
		$driverPhone=$request->input('driverPhone'); //获取司机手机号码
		$redis=new Redis();	//创建redis实例
        $redis->connect('localhost','6379'); //连接redis服务器
        $setFlag=(bool)$redis->sRem('driverPhone',$driverPhone); //从司机手机号码集合中删掉该司机手机号码
        $hashFlag=(bool)$redis->delete('startcar:'.$driverPhone);
        if ($setFlag && $hashFlag) {
            $flag=1;
        } else {
            $flag=0;
        }
        $array=[
            'flag'=>$flag
        ];

        return json_encode($array);
	}

	/**
	*调用场景：司机端，司机端定时向服务器获取订单推送
	*功能：将用车订单推送给周围的司机
	*请求方式：post
	*@var Redis $redis
    *@var bool $setFlag
    *@var bool $hashFlag
    *@var string $flag
    *@param string driverPhone
    *@return json $orders（订单编号、距离、人数、出发地、目的地、估价）
	*/
	public function pushOrder(Request $request)
	{
		$driverPhone=$request->input('driverPhone'); //获取司机手机号码
		$redis=new Redis();	//创建redis实例
        $redis->connect('localhost','6379'); //连接redis服务器
        //获取司机的车型和位置
        $carType=$redis->hGet('startcar:'.$driverPhone,'type'); //获取车型
        $driverPosition=$redis->hGet('startcar:'.$driverPhone,'driverPosition'); //获取位置
        //通过车型和位置匹配周围的订单，默认范围为2km
        $json=$this->pushFun($carType);
        $phones=json_decode($json);
        //将每一个乘客的详细信息拿出来
        $orders=array();
        foreach ($phones as $phone) {
            $order=$redis->hGetAll('usecar:'.$phone);
            if (!is_null($order)) {
                $orders[]=array('userPhone'=>$phone,'from'=>$order['from'],'destination'=>$order['destination'],'fromPosition'=>$order['fromPosition'],'passengeNum'=>$order['passengeNum']);
            }
            
        }
        return json_encode($orders);
	}

    /**
    *功能：根据司机车型和位置，匹配周围订单，返回订单对于乘客手机号码集合
    *@param string $carType 车型
    *@param int $len 匹配范围
    *@return json 乘客手机号码的集合
    */
	public function pushFun($carType,$len=2)
	{
        $phones=array();
        $phones[]='15218190853';
        //$phones[]='13888888888';

        return json_encode($phones);
	}

    /** 
    *调用场景：司机端，司机点击接单后
    *功能：判断司机是否抢到订单
    *请求方式：post
    *@var Redis $redis
    *@var string $flag
    *@param string driverPhone
    *@param string userPhone
    *@return json flag 成功与否标志,message 返回乘客端信息（乘客位置（经纬度）、出发地、目的地、人数、乘客姓名、乘客电话）
    */
    public function getOrder(Request $request)
    {
        $driverPhone=$request->input('driverPhone');
        $userPhone=$request->input('userPhone');
        if (is_null($userPhone)|| is_null($driverPhone) || $userPhone=='' || $driverPhone=='') {
            $array=[
                'flag'=>'0'
            ];
            return json_encode($array);
        }
        $redis=new Redis(); //创建redis实例
        $redis->connect('localhost','6379'); //连接redis服务器
        $isAccept=$redis->hGet('usecar:'.$userPhone,'isAccept');
        if ($isAccept!='1') {
           $array=[
                'flag'=>'0'
            ];
            return json_encode($array); 
        }
        else{
            $hashFlag=$redis->hSet('usecar:'.$userPhone,'isAccept','2');
            $user=$redis->hGetAll('usecar:'.$userPhone);
            $userMes=array('userPhone'=>$userPhone,'from'=>$user['from'],'destination'=>$user['destination'],'fromPosition'=>$user['fromPosition'],'passengeNum'=>$user['passengeNum'],'name'=>$user['name']);
            $array=[
                'flag'=>'1',
                'message'=>$userMes
            ];
            return json_encode($array);  
        }

    }

    /** 
    *调用场景：司机端，司机点击结束行程后
    *功能：修改订单状态为已结束行程，以让乘客端付款
    *请求方式：post
    *@var Redis $redis
    *@var string $price
    *@param string userPhone
    *@param string pathLength
    *@return json flag 成功与否标志，price 价格
    */
    public function finishOrder(Request $request)
    {
        $userPhone=$request->input('userPhone');
        $pathLength=$request->input('pathLength');
        if (is_null($userPhone)|| is_null($pathLength) || $userPhone=='' || $pathLength=='') {
            $array=[
                'flag'=>'0'
            ];
            return json_encode($array);
        }
        $redis=new Redis(); //创建redis实例
        $redis->connect('localhost','6379'); //连接redis服务器
        $status=$redis->hGet('usecar:'.$userPhone,'isAccept');
        if ($status=='4') { //订单是已完成状态
           $array=[
            'flag'=>'0'
        ];
        return json_encode($array); 
        }
        //修改订单状态为已完成状态
        $redis->hSet('usecar:'.$userPhone,'isAccept','4');
        //根据行程距离计算价格
        $price=$this->getPrice($pathLength);
        $array=[
            'flag'=>'1',
            'price'=>$price
        ];
        return json_encode($array);


    }

    public function getPrice($pathLength)
    {
        return '5';
    }



}
