<?php

namespace app\Api\Driver;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Redis;
use App\Driver;
use Config;
use JWTAuth;
use App\User;
use App\Api\Controllers\LoginController;

class RegisterController extends BaseController
{
  /**
   *@author Arius
   *@function login in
   *
   *@param tel password
   *@return status and token
   */
    public function login(Request $request){
      $tel = $request->input('tel');
      $password = $request->input('password');
      if($last = Driver::where('driverPhone','=',$tel)->first()) {
        if (sha1($password) == $last['password']) {
          $last['now'] = time();
          $last['lastip'] = $_SERVER['REMOTE_ADDR'];
          $last['secret'] = "wearevtmers";
          $last['random'] = rand(1000000,10000000);
          $token = JWTAuth::fromUser($last);
          $arr = array ('status'=>'SUCCESSED','token'=>$token);
          return $arr;
        }
        $arr = array ('status'=>"ERROR PASSWORD OR TEL");
        return response()->json(compact('arr'));
      }
      $arr = array ('status'=>"TEL NOT EXISTED");
      return response()->json(compact('arr'));
    }
  /**
   *@author Arius
   *@function send sms and set session
   *
   *@param tel number
   *@return return status json
   */
    public function sms(Request $request){
      $time = strtotime(date('Y-m-d H:i:s',time()));//integer
      $time = $time%10000;
      $value = array ('lastip'=>$_SERVER['REMOTE_ADDR'],'tel'=>$tel);
      $num = 'k'.strval($time);
      Session::put($num, $value);
      $tel = $request->input('tel');
      $result = LoginController::message($time,$tel);
      return $result;
    }
  /**
   *@author Arius
   *@function store message
   *
   *@param tel number
   *@return return status json
   */
   public function storeDriver(Request $request){
     $num = $request->input('num');
     $password = $request->input('password');
     $tel = $request->input('tel');
     $ip = $_SERVER['REMOTE_ADDR'];
     if ($value = Session::get($num)) {
       $session_ip = $value['lastip'];
       $session_tel = $value['tel'];
       if ($session_ip!=$_SERVER['REMOTE_ADDR']) {
         $arr = array ('status'=>"ERROR REQUEST");
         return response()->json(compact('arr'));
       }
       if ($session_tel!=$tel) {
         $arr = array ('status'=>"ERROR TEL");
         return response()->json(compact('arr'));
       }
       if ($usr = Driver::where("driverPhone","=",$tel)->first()) {
         $arr = array ('status'=>"TEL EXIST");
         return response()->json(compact('arr'));
       }
       $input['driverPhone'] = $tel;
       $input['password'] = $password;
       $result = Driver::create($input);
       $usr = Driver::where("driverPhone","=",$tel)->first();
       $usr['now'] = time();
       $usr['lastip'] = $_SERVER['REMOTE_ADDR'];
       $usr['secret'] = "wearevtmers";
       $usr['random'] = rand(1000000,10000000);
      //  return $usr;
       Config::set('jwt.user', 'App\Driver');
       Config::set('auth.providers.users.model', \App\Driver::class);
       $token = JWTAuth::fromUser($usr);
       $arr = array ('status'=>$result,'token'=>$token);
       return $arr;
    //  }
     $arr = array ('status'=>"ERROR NUM");
     return response()->json(compact('arr'));
    }
  /**
   *@author Arius
   *@function store message
   *
   *@param tel number
   *@return return status json
   */
    public function storeDetail(Request $request){
      $value = JWTAuth::toUser();
      $tel = $value['driverPhone'];
      // $tel = "1234";
      $idFrontPhoto = $request->input('idFrontPhoto');
      $idBehindPhoto = $request->input('idBehindPhoto');
      $motoFronPhoto = $request->input('motoFrontPhoto');
      $motoSidePhoto = $request->input('motoSide_Photo');
      $withIDPhoto = $request->input('withIDPhoto');
      $head = $request->input('head');
      $name = $request->input('name');
      $idNum = $request->input('idNum');
      $bankCard = $request->input('bankCard');
      $head = $request->input('head');
      $motoNum = $request->input('motoNum');
      $license = $request->input('license');
      $input['name'] = $name;
      $input['idNum'] = $idNum;
      $input['bankCard'] = $bankCard;
      $input['head'] = $head;
      $input['motoNum'] = $motoNum;
      $input['license'] = $license;
      if(!file_exists($tel)){
        mkdir($tel);
      }
      $result = RegisterController::fileCreate($idFrontPhoto,'idFrontPhoto',$tel);
      $input['idFrontPhoto'] = public_path().'/'.$tel.'/idFrontPhoto.jpg';
      $result = RegisterController::fileCreate($idBehindPhoto,'idBehindPhoto',$tel);
      $input['idBehindPhoto'] = public_path().'/'.$tel.'/idBehindPhoto.jpg';
      $result = RegisterController::fileCreate($head,'head',$tel);
      $input['head'] = public_path().'/'.$tel.'/head.jpg';
      $result = RegisterController::fileCreate($motoFrontPhoto,'motoFrontPhoto',$tel);
      $input['motoFrontPhoto'] = public_path().'/'.$tel.'/motoFrontPhoto.jpg';
      $result = RegisterController::fileCreate($motoSidePhoto,'motoSidePhoto',$tel);
      $input['motoSidePhoto'] = public_path().'/'.$tel.'/motoSidePhoto.jpg';
      $result = RegisterController::fileCreate($withIDPhoto,'withIDPhoto',$tel);
      $input['withIDPhoto'] = public_path().'/'.$tel.'/withIDPhoto.jpg';
      $driver = Driver::where('driverPhone','=',$tel)->first();
      $result = $driver->update($input);
      return $result;
    }
  /**
   *@author Arius
   *@function store file
   *
   *
   */
    protected function fileCreate($data,$name,$tel){
      $file = base64_decode(str_replace('data:image/jpg;base64,', '', $data));
      $result=file_put_contents($tel.'/'.$name.'.jpg', $file);
      // $result = $file->move( public_path().'/photo/project', $time.".".$file->getClientOriginalExtension());
      return $result;
    }

}
