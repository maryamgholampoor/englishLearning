<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    public function updateProfile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'nullable|string|max:15',
            'name'          => 'nullable|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'email'         => 'nullable|email|unique:users,email,' . $id,
            'birth_date'    => 'nullable|date',
            'sex'           => 'nullable|in:male,female,other',
            'profile_pic'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // مقادیر فقط در صورتی که مقدار جدیدی ارسال شده باشد، آپدیت شوند
        $user->update($request->except(['profile_pic']));

        // آپلود تصویر پروفایل
//        if ($request->hasFile('profile_pic')) {
//            $profilePicPath = $request->file('profile_pic')->store('profile_pics', 'public');
//            $user->profile_pic = $profilePicPath;
//        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
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

//    public function updateProfile(Request $request)
//    {
//        $user = auth()->user();
//
//        // Validate request
//        $this->validate($request, [
//            'mobile_number' => ['nullable', 'string', 'regex:/^09\d{9}$/', 'unique:users,mobile_number,' . $user->id],
//            'user_status' => ['nullable', 'integer', 'in:0,1'],
//            'last_name' => ['nullable', 'string', 'max:255'],
//            'name' => ['nullable', 'string', 'max:255'],
//            'email' => ['nullable', 'email', 'unique:users,email,' . $user->id],
//            'birth_date' => ['nullable', 'date', 'before:today'],
//            'sex' => ['nullable', 'string', 'in:male,female,other'],
//            'profile_pic' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // Max 5MB
//        ]);
//
//        try {
//            DB::beginTransaction();
//
//            // Update user details
//            $user->update($request->except(['profile_pic']));
//
//            // Handle profile picture upload
//            if ($request->hasFile('profile_pic')) {
//                $file = $request->file('profile_pic');
//                $path = $file->store('profile_pictures', 'public'); // Store in storage/app/public/profile_pictures
//                $user->profile_pic = $path;
//                $user->save();
//            }
//
//            DB::commit();
//            return $this->sendJsonResponse($user, trans('message.profile_updated_successfully'), $this->getStatusCodeByCodeName('OK'));
//
//        } catch (\Exception $exception) {
//            DB::rollBack();
//            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('INTERNAL_SERVER_ERROR'));
//        }
//    }

}
