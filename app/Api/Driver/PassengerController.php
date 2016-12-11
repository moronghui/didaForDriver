<?php

namespace app\Api\Driver;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class PassengerController extends BaseController
{
	//功能：获取乘客信息、路线导航
	
	/**
     * The authentication guard that should be used.
     *
     * @var string
     */
    public function __construct()
    {
        parent::__construct();

    }
}

