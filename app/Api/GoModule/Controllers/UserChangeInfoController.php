<?php

namespace App\Api\GoModule\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Requests;
use App\Http\Requests\changeUserInfoRequest;
use JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Curl\Curl;;
use TopClient;
use ResultSet;
use RequestCheckUtil;
use TopLogger;
use AlibabaAliqinFcSmsNumSendRequest;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Response;
use App\Wechat;

class UserChangeInfoController extends BaseController
{

    /**
    *
    *
    *@function 修改乘客信息
    *
    *@param $request
    */

    public function changeInfo(changeUSerInfoRequest $request)
    {
	
        $updateData['tel'] = $request->input('tel');
        $updateData['nickname'] = $request->input('nickname');
        if(empty( $updateData )){
 	    $result = $this->returnMsg('204','data is empty');
    	    return response()->json($result);
	}
       
	$num = $request->input('num');
	$num = 'k'.strval($num);
	$value = Session::get($num, 'default');
	
	if ($value != 'default') {
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		DB::table('wechats')
		    ->where('openid',$user['openid'])
		    ->update($updateData);
		Session::forget($num);
		$result = $this->returnMsg('200','ok');
		return response()->json($result);

	}else {
	        $result = $this->returnMsg('500','ERROR CODE');
        	return response()->json($result);
	}

    }
    /**
     *@author Arius
     *@function set a session for tel number checking
     *@param tel
     *@return sms sending status
     */
    public function sessionSet(Request $request)
    {
      $time = strtotime(date('Y-m-d H:i:s',time()));//integer
      $time = $time%10000;
      $value = array ('lastip'=>$_SERVER['REMOTE_ADDR'],'tel'=>$request->input('tel'));
      $num = 'k'.strval($time);
      Session::put($num, $value);

      $result=UserChangeInfoController::message($time,$request['tel']);

      return $result;
    }

        /**
            *@author Arius
            *@function send sms combined with Alidayu
            *@param checking code ,sms number
            *@todo  ip update for Alidayu white list
            *                               */
        public function message($num,$tel){
        $c = new TopClient();
        $c->appkey = "23553742";  //  App Key的值 这个在开发者控制台的应用管理点击你添加过的应用就有了
        $c->secretKey = "170f0500f220c2b61a95c2e9065a6670"; //App Secret的值也是在哪里一起的 你点击查看就有了
        $req = new AlibabaAliqinFcSmsNumSendRequest();
        $req->setExtend(""); //这个是用户名记录那个用户操作
        $req->setSmsType("normal"); //这个不用改你短信的话就默认这个就好了
        $req->setSmsFreeSignName("滴达"); //这个是签名
        $req->setSmsParam("{'code':'".$num."'}"); //这个是短信签名
        $req->setRecNum($tel); //这个是写手机号码
        $req->setSmsTemplateCode("SMS_32485128"); //这个是模版ID 主要也是短信内容
        $resp = $c->execute($req);
        $resp = json_encode($resp);
        $resp = json_decode($resp);
        if(isset($resp->result)){
            if($resp->result->err_code == 0){
              $result = $this->returnMsg('200','OK');
              return response()->json($result);
            }
        }
            $result = $this->returnMsg('500',$resp);
            return response()->json($result);
        }




}

