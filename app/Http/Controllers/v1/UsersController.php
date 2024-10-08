<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Models\Padcast;
use App\Models\PadcastCategory;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersController extends Controller
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

    public function showAllUsers()
    {
        DB::beginTransaction();
        try {
            $users = User::all();
            return $this->sendJsonResponse($users, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

//        $users->each(function ($users)
//        {
//
//            $users->attributes['sub']=1;
//        }
//        );
    }

    public function editUsers(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;

            $this->rules = ['user_id'=>'required|integer|exists:Users,id',
                             'name'=>'string|max:255',
                             'last_name'=>'string|max:255',
                             'email'=>'email|unique:users,email',
                             'birth_date'=>'date',
                             'sex'=>'integer'];

            if (!$this->validateRequest($request->all())) {
                return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
            }
            // Find the user by ID
            $user = User::find($user_id);
            if (!$user) {
                return $this->sendJsonResponse([], trans('message.user_not_found'), $this->getStatusCodeByCodeName('Not Found'));
            }

            // Update user data
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->birth_date = $request->birth_date;
            $user->sex = $request->sex;
            $user->save();

            DB::commit();
            return $this->sendJsonResponse($user, trans('message.user_updated_successfully'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteUser(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;

            // Check if the user exists
            $user = User::find($user_id);
            if (!$user) {
                return $this->sendJsonResponse([], trans('message.user_not_found'), $this->getStatusCodeByCodeName('Not Found'));
            }

            // Delete the user
            $user->delete();

            DB::commit();
            return $this->sendJsonResponse([], trans('message.user_deleted_successfully'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showProfile(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;
            $user_info = User::where('id', $user_id)->get();

            return $this->sendJsonResponse($user_info, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        }
        catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

}
