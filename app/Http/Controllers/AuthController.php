<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Input;
use Redirect;
use Auth;
use Cookie;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
	
	//public function __construct(){
	//$this->middleware( 'auth' );}			****把这一段加到projectscontroller中，执行方法前先通过用户登陆验证，你测试下不知道行不行****
	

	/*****	路由*****
	Route::get('/login',function(){
		return view('test');
		});				表单自己创建下，测试一下

	Route::post('/login','AuthController@login');
	Route::get('/logout','AuthController@logout');
	/**************/

	public function login(Request $request)
	{	
		$email = $request->email ; 
		$password = $request->password; 

		//return  $email;
	 
	 	if ( Auth::attempt([ 'email' => $email, 'password' => $password ]))
		{
			$user = ["email" => $email];
			Cookie::queue("TOKEN", json_encode($user), 3600);
			//return 1;
			return Redirect::to( '/works' );  
			//->with('true')               
	 		}
	 	else
	 	{
	 		return 0;
	 		//return Redirect::to('login')->withErrors($error);
	  		//return Redirect::to( 'login' )->with( 'false' );  
	 		} 
	}

	public function logout()
	{
		Auth::logout();
		return  Redirect::guest( 'login' );
	}
	
/*	protected function create(array $data)
	{
		return User::create([
	            'name' => $data['name'],
	            'email' => $data['email'],
	            'password' => bcrypt($data['password']),
	            ]);
	}*/
}
