<?php

namespace app\Api\Driver;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class DeliveryController extends BaseController
{
	//功能：获取物件信息，路线导航、验收物件、物件签收

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
