<?php
use App\User;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
//driver
$api->group(['middleware' =>'web','prefix'=>'Driver'], function ($api) {
  $api->post('login', 'RegisterController@login');
  $api->post('stordr', 'RegisterController@storeDriver');
    $api->group(['middleware' => 'jwt.api.auth'], function ($api) {
        $api->post('storde', 'RegisterController@storeDetail');
        $api->get('start', 'driver\CommonController@start');
        $api->get('end', 'driver\CommonController@end');
        $api->get('pushOrder', 'driver\CommonController@pushOrder');
        $api->get('getOrder', 'driver\CommonController@getOrder');
        $api->get('finishOrder', 'driver\CommonController@finishOrder');
    });
  });
});
});
