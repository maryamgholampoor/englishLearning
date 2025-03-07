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
use Illuminate\Support\Facades\File;


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

            $this->rules = ['user_id' => 'required|integer|exists:Users,id',
                'name' => 'string|max:255',
                'last_name' => 'string|max:255',
                'email' => 'email|unique:users,email',
                'birth_date' => 'date',
                'sex' => 'integer'];

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
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'last_name' => 'string|max:255',
                'email' => 'email|unique:users,email,' . $id,
                'birth_date' => 'date',
                'sex' => 'in:male,female,other',
            ]);

            if (!$this->validateRequest($request->all())) {
                return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Acceptable'));
            }

            $user = User::where('id', $id)->first();
            if (!$user) {
                if (!$this->validateRequest($request->all())) {
                    return $this->sendJsonResponse([], $this->validation_messages, $this->getStatusCodeByCodeName('Not Found'));
                }
                return response()->json(['error' => 'User not found'], 404);
            }

            if ($request->name != null) {
                $user->name = $request->name;
            }else{
                $user->name = null;

            }
            if ($request->last_name != null) {
                $user->last_name = $request->last_name;
            }
            else{
                $user->last_name=null;
            }
            if ($request->email != null) {
                $user->email = $request->email;
            }
            else{
                $user->email=null;
            }
            if ($request->birth_date != null) {
                $user->birth_date = $request->birth_date;
            }
            else{
                $user->birth_date=null;
            }
            if ($request->sex != null) {
                $user->sex = $request->sex;
            }else{
                $user->sex =null;
            }

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
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function updateProfilePicture(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse([], $validator->errors(), $this->getStatusCodeByCodeName('Not Acceptable'));
            }

            $users = User::where('id', $id)->first();
            if (!$users) {
                return $this->sendJsonResponse([], trans('message.user_not_found'), $this->getStatusCodeByCodeName('Not Found'));
            }

            $image = $request->file('profile_picture');
            $fileName = time() . '_' . $image->getClientOriginalName();
            $path = app()->basePath('public/uploads/profile_pictures' . DIRECTORY_SEPARATOR);

            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true);
            }

            if ($users->profile_picture && File::exists(app()->basePath("public/{$users->profile_picture}"))) {
                File::delete(app()->basePath("public/{$users->profile_picture}"));
            }

            $image->move($path, $fileName);
            $path_file = "uploads/profile_pictures/$fileName";

            $users->profile_pic = $path_file;
            $users->save();

            DB::commit();
            return $this->sendJsonResponse(['profile_picture_url' => url($path_file)], trans('message.profile_picture_updated'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
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
