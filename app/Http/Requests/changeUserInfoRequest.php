<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class changeUserInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tel'=>'required|max:11',
            'nickname'=>'required|max:30'
        ];
    }

    public function message(){
        return [
                'tel.required' => 'A tel is required',
                'nickname.required'  => 'A nickname is required',
                'nickname.max'  => 'nickname max is  30',
                'tel.max'  => 'tel max is  11',

        ];
    }
}
