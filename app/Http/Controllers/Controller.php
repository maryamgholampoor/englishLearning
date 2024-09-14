<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;


class Controller extends BaseController
{
    protected $rules = [];
    protected $validation_messages = [];
    protected $model;

    protected function validateRequest(array $params)
    {
        $validate = Validator::make($params, $this->rules);
        if ($validate->fails())
        {
            $this->validation_messages = $validate->errors();
            return false;
        }
        return true;
    }
}
