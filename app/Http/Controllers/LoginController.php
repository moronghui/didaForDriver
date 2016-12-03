<?php

namespace App\Http\Controllers;

use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Wechat;

class LoginController extends BaseController
{
    public function index(){
      $appid = "wx1aabdf768c60315f";
      $redirect_uri = urlencode("http://".$_SERVER['HTTP_HOST']."/api/info");
      //不弹窗取用户openid，snsapi_base;弹窗取用户openid及详细信息，snsapi_userinfo;
      $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
      return redirect($url);
    }
    public function info(){
      $appid = "wx1aabdf768c60315f";
      $appsecret = "89cdb8aef0b3de54bf7b9d1d42364c47";
      $code = $_GET['code'];
      // $code = "0218brh71m28RQ1P6bh715Nrh718brhA";
      $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
      $curl = new Curl();
      $curl->get($url);
      $response = $curl->response;
      $response = json_decode($response,true);
      $access_token = $response['access_token'];
      $openid = $response['openid'];
      $url_info ="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
      $curl->get($url_info);
      $info = $curl->response;
      $info = json_decode($info,true);
      LoginController::store($info);
      $curl->close();
      return $openid;
    }
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
    public function test(){
      $curl = new Curl();
      // $curl->get('www.obstacle.cn:7007/api/works');
      // $response = $curl->response;
      // $response = json_encode($response,true);
      // $response = json_decode($response,true);
      $arr = array ('status'=>"success");
      return response()->json(compact('arr'));
    }
}
