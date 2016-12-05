<?php

namespace App\Api\Controllers;

use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Wechat;
use JWTAuth;
use TopClient;
use ResultSet;
use RequestCheckUtil;
use TopLogger;
use AlibabaAliqinFcSmsNumSendRequest;

class LoginController extends BaseController
{

  /**
   *@author Arius
   *@function to get wechat code
   *
   *@return  redirect a url with the code
   */
    public function index(){
      $appid = "wx1aabdf768c60315f";
      $redirect_uri = urlencode("http://".$_SERVER['HTTP_HOST']."/api/code");
      //不弹窗取用户openid，snsapi_base;弹窗取用户openid及详细信息，snsapi_userinfo;
      $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
      return redirect($url);
    }
    /**
     *@author Arius
     *@function to get wechat users detail and store
     *@param wechatcode and remote ip
     *@return  user openid
     */
    public function info($code,$ip){
      $appid = "wx1aabdf768c60315f";
      $appsecret = "89cdb8aef0b3de54bf7b9d1d42364c47";
      $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
      $curl = new Curl();
      $curl->setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);
      $curl->get($url);
      $response = $curl->response;

      $response = json_decode($response,true);

      $access_token = $response['access_token'];
      
      $openid = $response['openid'];
      $url_info ="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
      $curl->get($url_info);
      $info = $curl->response;
      $info = json_decode($info,true);
      $info['lastip'] = $ip;
      LoginController::store($info);
      $curl->close();
      return $openid;
    }
    /**
     *@author Arius
     *@function store wechat usr
     *@param array of user information
     *
     */
    public function store($request){
      $openid = $request['openid'];
      $user = wechat::where("openid","=",$openid)->first();
      if ($user == null) {
        Wechat::create($request);
      }
      else {
        $user->update($request);
      }
      return "HAVED STORED!!";
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
      $value = array ('lastip'=>$_SERVER['REMOTE_ADDR'],'tel'=>$request['tel']);
      $num = 'k'.strval($time);
      Session::put($num, $value);
      // Session::flush();
      // $data = Session::all();
      // return $data;
      $result=LoginController::message($time,$request['tel']);
      return $result;
    }

    /**
     *@author Arius
     *@function send sms combined with Alidayu
     *@param checking code ,sms number
     *
     *@todo  ip update for Alidayu white list
     */
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
    	return response()->json(compact('resp'));
    }
    /**
     *@author Arius
     *@function check code by session and set tel
     *@param checking code
     *
     *
     */
    public function check(Request $request)
    {
      $num = $request['num'];
      $num = 'k'.strval($num);
      $usr = JWTAuth::toUser();
      $ip = $usr['lastip'];
      $value = Session::get($num, 'default');
      // return $value;
      if ($value != 'default') {
        if ($value['lastip']==$ip) {
          $usr['tel']=$value['tel'];
          $input=array();
          $input['openid']=$usr['openid'];
          $input['tel']=$usr['tel'];
          // return $input;
          LoginController::store($input);
          Session::forget($num);
          JWTAuth::refresh();
          $usr['now'] = time();
          $usr['secret'] = "wearevtmers";
          $usr['random'] = rand(1000000,10000000);
          // return $usr;
          $token = JWTAuth::fromUser($usr);
          return response()->json(compact('token'));
        }
        $arr = array ('ERROR'=>"ERROR CODE");
        return response()->json(compact('arr'));
      }
      $arr = array ('ERROR'=>"ERROR CODE");
      return response()->json(compact('arr'));
    }
    /**
     *@author Arius
     *@function wechat code test
     *
     *
     *
     */
    public function code($request){
      $arr = array ('code'=>$_GET['code']);
      return response()->json(compact('arr'));
    }


    public function test(){
      $curl = new Curl();//测试Curl
      // $curl->get('www.obstacle.cn:7007/api/works');
      // $response = $curl->response;
      // $response = json_encode($response,true);
      // $response = json_decode($response,true);
      return JWTAuth::toUser();
      $arr = array ('status'=>"success");
      return response()->json(compact('arr'));
    }
}
