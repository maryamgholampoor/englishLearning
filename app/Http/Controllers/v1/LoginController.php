<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    use Response, StatusCode, UtilityRequest;

    public function __construct()
    {

    }

    public function doLogin(Request $request)
    {

        if (!$this->validateRequest($request->all())) {
            return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
        }


        // Insert data
        DB::beginTransaction();
        try {
            // Check mobile number exist
            $user = User::where('mobile_number',$request->mobile_number)->first();
            if (!$user) {
                $user = User::create(['mobile_number' => $request->mobile_number, 'user_status' => User::USER_ACTIVE]);
            }
            // Create login code and send it
            $code = $this->randomDigits(5);
            $expiration = date('Y-m-d H:i:s', strtotime('+3 minutes'));
            LoginCode::create(['code' => $code, 'user_id' => $user->id, 'expiration_time' => $expiration]);
            $this->sendLoginCode($request->mobile_number, $code);
            // Commit transaction
            DB::commit();
            // Return response
            return $this->sendJsonResponse(['user' => $user], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function checkLoginCode(Request $request)
    {
        // Check validation
        $this->rules = ['code' => 'required|string|max:5', 'user_id' => 'required|integer'];
        if (!$this->validateRequest($request->all())) {
            return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
        }
        // Check code
        try {
            $result = LoginCode::findByCode($request->user_id, $request->code, date('Y-m-d H:i:s'))->first();
            if (!$result) {
                return $this->sendJsonResponse([], trans('message.there_is_no_login_code_with_specific'), $this->getStatusCodeByCodeName('Bad Request'));
            }
            // Save result
            $result->used_time = date('Y-m-d H:i:s');
            $result->save();
            // Redirect to register form or panel form
//            $userRegistered = !($result->user->user_type == User::USER_TYPE_TEMPORARY);
            // Generate Token if needed
            $token = JWTAuth::fromUser($result->user);
            // Return response
            return $this->sendJsonResponse([ 'token' => $token, 'token_type' => 'bearer'], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    private function randomDigits($length)
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= random_int(0, 9);
        }
        return $result;
    }

}
