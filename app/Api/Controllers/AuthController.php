<?php
namespace App\Api\Controllers;

use App\Wechat;
use Illuminate\Http\Request;
use JWTAuth;
use App\Api\ControllersLoginController;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;


class AuthController extends BaseController
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $code = $request->get('code');
        $openid = new LoginController();
        $openid = $openid->info($code);
        $user = wechat::where("openid","=",$openid)->first();
        // if ($user == null) {
        //   return "没有";
        // }
        // else {
        //   return $user;
        // }
        // $user = wechat::find(1);
        // $user = $user[0];
        if (!$user){
          $arr = array ('status'=>"NO USER");
          return response()->json(compact('arr'));
        }
        $user['now'] = time();
        $user['secret'] = "wearevtmers";
        $user['random'] = rand(1000000,10000000);
        // return $user;
        $token = JWTAuth::fromUser($user);
        return response()->json(compact('token'));
    }

    /**
     * @param Request $request
     */
/*    public function register(Request $request)
    {
        $newUser = [
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'password' => bcrypt($request->get('password'))
        ];
        $user = Client::create($newUser);
        $token = JWTAuth::fromUser($user);
        return $token;
    }  */

    public function logout(){
      // $token = $request->get('token');
        JWTAuth::refresh();
        $arr = array ('LOG OUT'=>"SUCCESSED");
        return response()->json(compact('arr'));
    }
    /****
     * 获取用户的信息
     * @return \Illuminate\Http\JsonResponse
     */

}
