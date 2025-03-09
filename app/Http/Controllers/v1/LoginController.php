<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Models\Admin;
use DateTime;
use DateTimeZone;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;


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

    protected $jwt_secret="ojoijoiiiji";

    public function doLogin(Request $request)
    {
        $this->validate($request, [
            'mobile_number' => [
                'required',
                'string',
                'regex:/^(\+98|0)?9\d{9}$/',
            ],
        ]);

        DB::beginTransaction();
        try {
            $user = User::where('mobile_number',$request->mobile_number)->first();
            if (!$user) {
                $user = User::create(['mobile_number' => $request->mobile_number, 'user_status' => User::USER_ACTIVE]);
            }
            // Create login code and send it
            $code = $this->randomDigits(5);
            $expiration = date('Y-m-d H:i:s', strtotime('+3 minutes'));
            LoginCode::create(['code' => $code, 'user_id' => $user->id, 'expiration_time' => $expiration]);
            $this->sendMessageRegisterCompleted($user , $code ,"3233");
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
        $this->rules = [
            'code' => [
                'required',
                'string',
                'size:5',
                'regex:/^\d{5}$/',
            ],
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ];

        if (!$this->validateRequest($request->all())) {
            return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
        }
        // Check code
        try {
            $users = User::where('id',$request->user_id)->first();

            $result = LoginCode::where('user_id',$request->user_id)->where('code',$request->code)->where('used_time',null)->first();
            if (!$result) {
                return $this->sendJsonResponse([], trans('message.there_is_no_login_code_with_specific'), $this->getStatusCodeByCodeName('Bad Request'));
            }

            // Save result
            $result->used_time = date('Y-m-d H:i:s');
            $result->save();

            // Generate JWT Token
            $payload = [
                'iss' => "lumen-jwt", // Issuer of the token
                'sub' => $request->user_id, // Subject of the token (admin ID)
                'iat' => time(), // Issued at
                'exp' => time() + 60 * 60 // Token expiration time (1 hour)
            ];

            $token = JWT::encode($payload, $this->jwt_secret, 'HS256');
            // Return response
            return $this->sendJsonResponse(['users'=> $users ,'token' => $token, 'token_type' => 'bearer'], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
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

    public function login(Request $request)
    {
        // Validate request
        $this->validate($request, [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:8', // Enforces a minimum length for security
                'max:64', // Prevents excessively long passwords
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ],
        ]);

        $admin = Admin::where('email', $request->email)->first();
//
//      return Hash::make("Kh6040@h");

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate JWT Token
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $admin->id, // Subject of the token (admin ID)
            'iat' => time(), // Issued at
            'exp' => time() + 60 * 60 // Token expiration time (1 hour)
        ];

        $token = JWT::encode($payload, $this->jwt_secret, 'HS256');

        return $this->sendJsonResponse(['token' => $token,'admin'=>$admin], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

    }

    private function getAuthenticatedAdmin($request)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return null;
        }

        try {
            $decoded = JWT::decode(str_replace('Bearer ', '', $token), new Key($this->jwt_secret, 'HS256'));
            return Admin::find($decoded->sub);
        } catch (\Exception $e) {
            return null;
        }
    }

}
