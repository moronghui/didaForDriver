<?php

namespace App\Api\GoModule\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Requests;
use App\Http\Requests\changeUserInfoRequest;
use JWTAuth;
use Illuminate\Support\Facades\DB;
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
        $data = $request->input();
        if(!empty($data)){
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        DB::table('wechats')
            ->where('openid',$user['openid'])
            ->update($data);
        $result = $this->returnMsg('200','ok');
        return response()->json($result);

        }
        $result = $this->returnMsg('204','data is empty');
        return response()->json($result);

    }
}

