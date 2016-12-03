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
    protected function formatValidationErrors(Validator $validator)
    {
        $message = $validator->errors()->first();
        return ['message'=>$message, 'status_code' => 500];
    }
}
