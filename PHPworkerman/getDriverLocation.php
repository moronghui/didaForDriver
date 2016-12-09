<?php
use Workerman\Worker;
use \Workerman\Lib\Timer;
require_once './Workerman/Autoloader.php';
// 初始化一个worker容器，监听1234端口
$worker = new Worker('websocket://0.0.0.0:9999');
// 这里进程数必须设置为1
$worker->count = 1;
// worker进程启动后建立一个内部通讯端口
$worker->onWorkerStart = function($worker)
{
	echo "worker runing";
};
// 新增加一个属性，用来保存token到connection的映射
$worker->tokenConnections = array();
// 当有客户端发来消息时执行的回调函数
$worker->onMessage = function($connection, $data)use($worker)
{
    $data = json_decode($data,true);
    $token = $data['token'];
   
    // 判断当前客户端是否已经验证,既是否设置了token
  
       
       $connection->token = $token;
       $driverPosition=$data['driverPosition'];
	
       

	
	 
       /* 保存token到connection的映射，这样可以方便的通过token查找connection，
        * 实现针对特定token推送数据
        */

	
       $worker->tokenConnections[$connection->token] = $connection;
// 获取$token,先认证token是否已经登录，如果没有登录，返回未登录；查询是否有这个订单，然过没有，返回没有这个订单；然后，若通过，通过手机号码取出司机的位置，并且返回；
//checkDriverLogin（）通过token检查司机是否已经登录。
    if(checkDriverLogin($token) != 'ok' && $driverPosition != '')
    {

		$redis = new redis();
		$redis->connect('127.0.0.1', 6379);
		$redis->hset("driver:".$driverPhone,driverPosition,$driverPosition);
		
		// 通过workerman，向token的页面推送数据
		$ret = sendMessageByUid($token, 'ok');
	});

	
    }else{
	$result['code'] ="204";
	$result['message'] = "unlogin";
	$ret = sendMessageByUid($token,$result['code']);
    }
};

// 当有客户端连接断开时
$worker->onClose = function($connection)use($worker)
{
    global $worker;
    if(isset($connection->token))
    {
        // 连接断开时删除映射
        unset($worker->tokenConnections[$connection->token]);
	echo "$connection->token断开连接";
    }
};

// 向所有验证的用户推送数据
function broadcast($message)
{
   global $worker;
   foreach($worker->tokenConnections as $connection)
   {
        $connection->send($message);
   }
}

// 针对token推送数据
function sendMessageByUid($token, $message)
{
    global $worker;
    if(isset($worker->tokenConnections[$token]))
    {
        $connection = $worker->tokenConnections[$token];
        $connection->send($message);
        return true;
    }
    return false;
}

function checkDriverLogin($token){

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_PORT => "8000",
	  CURLOPT_URL => "http://localhost:8000/api/checkDriverLocation",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_HTTPHEADER => array(
	    "authorization: Bearer  $token",
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  return $response;
	}
}

// 运行所有的worker（其实当前只定义了一个）
Worker::runAll();
