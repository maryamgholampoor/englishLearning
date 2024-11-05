<?php

namespace App\Http\Controllers\v1;

use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Http\Utilities\StatusCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Padcast;
use App\Models\PadcastCategory;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;

class PadcastController extends Controller
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

    public function addPadcastCategory(Request $request)
    {

        // Validate the request input
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:padcast_category,name',
            'description' => 'required|string',
            'view_count' => 'integer',
            'image' => 'required|file',
            'type' => 'required|in:text,voice',
        ]);

        try {
            $image = $request->file('image');
            $fileName = $image->getClientOriginalName();
            $path = app()->basePath('public/uploads/padcastCat' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('image')) {
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }
                $image->move($path, $fileName);
                $path_file = "uploads/padcast/$fileName";
            }
            // Start a transaction
            DB::beginTransaction();

            // Create a new Padcast Category
            $padcastCategory = new PadcastCategory();
            $padcastCategory->name = $request->input('name');
            $padcastCategory->description = $request->input('description');
            $padcastCategory->image_path = $path_file;
            $padcastCategory->type = $request->input('type');
            $padcastCategory->save();

            // Commit the transaction
            DB::commit();

            return $this->sendJsonResponse($padcastCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showPadcastCategory()
    {
        try {
            // Find the Padcast Category by ID
            $padcastCategory = PadcastCategory::get();
            DB::commit();
            return $this->sendJsonResponse($padcastCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function updatePadcastCategory(Request $request, $id)
    {
        // Validate the incoming request data
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:padcast_category,name',
            'description' => 'required|string',
            'type' => 'required',
        ]);

        try {
            $image = $request->file('image');
            if (isset($image)) {
                $image = $request->file('image');
                $fileName = $image->getClientOriginalName();
                $path = app()->basePath('public/uploads/padcastCat' . DIRECTORY_SEPARATOR);

                if ($request->hasFile('image')) {
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0777, true);
                    }
                    $image->move($path, $fileName);
                    $path_file = "uploads/padcast/$fileName";
                }
            }

            // Find the Padcast Category by ID
            $padcastCategory = PadcastCategory::findOrFail($id);

            // Update the category with the new data
            $padcastCategory->name = $request->input('name', $padcastCategory->name);
            $padcastCategory->description = $request->input('description', $padcastCategory->description);
            $padcastCategory->image_path = $path_file;
            $padcastCategory->type = $request->input('type', $padcastCategory->type);
            $padcastCategory->save();
            DB::commit();

            // Return a success response with the updated category data
            return $this->sendJsonResponse($padcastCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deletePadcastCategory($id)
    {
        try {
            $padcastCategory = PadcastCategory::find($id);

            if ($padcastCategory) {

                $padcastCategory->delete();
            } else {
                return $this->sendJsonResponse([], trans('Not Found'), $this->getStatusCodeByCodeName('OK'));
            }

            DB::commit();

            return $this->sendJsonResponse($padcastCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function addPadcast(Request $request)
    {
        // Validate the request input
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'time' => 'required|date_format:H:i:s',
            'bulk' => 'required|string|max:255',
            'image' => 'required',
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            $image = $request->file('image');
            $fileName = $image->getClientOriginalName();
            $path = app()->basePath('public/uploads/padcast' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('image')) {
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }
                $image->move($path, $fileName);
                $path_file = "uploads/padcast/$fileName";
            }

            // Create a new Padcast
            $padcast = new Padcast();
            $padcast->name = $request->input('name');
            $padcast->file_path = $path_file;
            $padcast->time = $request->input('time');
            $padcast->bulk = $request->input('bulk');
            $padcast->save();

            // Commit the transaction
            DB::commit();

            return $this->sendJsonResponse($padcast, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showPadcast()
    {
        try {
            $padcast = Padcast::get();
            DB::commit();

            return $this->sendJsonResponse($padcast, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function updatePadcast(Request $request, $id)
    {
        // Validate the request input
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'time' => 'required|date_format:H:i:s',
            'bulk' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $padcast = Padcast::find($id);

            if ($request->hasFile('image'))
            {

                $image = $request->file('image');
                $fileName = $image->getClientOriginalName();
                $path = app()->basePath('public/uploads/padcast' . DIRECTORY_SEPARATOR);

                if ($request->hasFile('image')) {
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0777, true);
                    }
                    $image->move($path, $fileName);
                    $path_file = "uploads/padcast/$fileName";
                    $padcast->file_path = $path_file;
                }
            }

            if ($request->has('name')) {
                $padcast->name = $request->input('name');
            }
            if ($request->has('time')) {
                $padcast->time = $request->input('time');
            }
            if ($request->has('bulk')) {
                $padcast->bulk = $request->input('bulk');
            }

            $padcast->save();

            DB::commit();

            return $this->sendJsonResponse($padcast, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deletePadcast($id)
    {
        try {
            DB::beginTransaction();
            $padcast = Padcast::find($id);

            if ($padcast) {

                $padcast->delete();

            } else {
                return $this->sendJsonResponse([], trans('Not Found'), $this->getStatusCodeByCodeName('OK'));
            }

            DB::commit();

            return $this->sendJsonResponse($padcast, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        }
        catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

}
