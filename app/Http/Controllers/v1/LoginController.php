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

        $url = "https://portal.amootsms.com/rest/SendWithPattern";

        $url = $url."?"."Token=".urlencode("8AADD086A29A2C589E380BE2D9BE20822D403B38");
        $url = $url."&"."Mobile=09038231952";
        $url = $url."&"."PatternCodeID=2844";
        $url = $url."&"."PatternValues=user_name,code";

        $json = file_get_contents($url);
        return $json;

//      $result = json_decode($json);
//      echo $result->Status;

//        if (!$this->validateRequest($request->all()))
//        {
//            return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
//        }
//
//        // Insert data
//        DB::beginTransaction();
//        try {
//            // Check mobile number exist
//            $user = User::where('mobile_number',$request->mobile_number)->first();
//            if (!$user) {
//                $user = User::create(['mobile_number' => $request->mobile_number, 'user_status' => User::USER_ACTIVE]);
//            }
//            // Create login code and send it
//            $code = $this->randomDigits(5);
//            $expiration = date('Y-m-d H:i:s', strtotime('+3 minutes'));
//            LoginCode::create(['code' => $code, 'user_id' => $user->id, 'expiration_time' => $expiration]);
//           return $this->sendLoginCode($request->mobile_number, $code);
//            // Commit transaction
//            DB::commit();
//            // Return response
//            return $this->sendJsonResponse(['user' => $user], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));
//        } catch (\Exception $exception) {
//            DB::rollBack();
//            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
//        }
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
            //$userRegistered = !($result->user->user_type == User::USER_TYPE_TEMPORARY);
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

    private $jwt_secret = "your_secret_key"; // Change this to a strong secret key

    public function login(Request $request)
    {
        // Validate request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Find admin by email
        $admin = Admin::where('email', $request->email)->first();

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

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'admin' => $admin
        ]);
    }

    public function profile(Request $request)
    {
        $admin = $this->getAuthenticatedAdmin($request);
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json($admin);
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
