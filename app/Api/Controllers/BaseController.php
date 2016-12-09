<?php

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Contracts\Validation\Validator;

class BaseController extends Controller
{
    use Helpers;

    /****
     * BaseController constructor.
     */
    public function __construct()
    {

    }

    public function returnMsg($code='200', $message='ok', $data=''){
        $arr['code'] = $code;
        $arr['message'] = $message;
        $arr['data'] = $data;
        return $arr;
    }
}
