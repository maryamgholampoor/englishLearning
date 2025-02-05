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
            'file' => 'required'
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $pathFile = app()->basePath('public/uploads/padcast' . DIRECTORY_SEPARATOR);

            if($request->hasFile('file'))
            {
                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
                $file->move($pathFile, $fileName);
                $path_file = "uploads/padcast/$fileName";
            }

            $sizeFile=File::size($path_file);

            $getID3 = new \getID3;
            $video_file = $getID3->analyze($path_file);
            $duration_seconds = $video_file['playtime_seconds'];
            $duration = date('H:i:s', $duration_seconds);

            // Create a new Padcast
            $padcast = new Padcast();
            $padcast->name = $request->input('name');
            $padcast->text = $request->input('text');
            $padcast->time = $duration;
            $padcast->bulk = $this->formatBytes($sizeFile);
            $padcast->file_path = $path_file;
            $padcast->padcastCategory_id = $request->input('padcastCategory_id');
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
            'text' => 'string',
            'padcastCategory_id'=> 'required'
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $pathFile = app()->basePath('public/uploads/padcast' . DIRECTORY_SEPARATOR);

            if($request->hasFile('file'))
            {
                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
                $file->move($pathFile, $fileName);
                $path_file = "uploads/padcast/$fileName";
            }

            $sizeFile=File::size($path_file);

            $getID3 = new \getID3;
            $video_file = $getID3->analyze($path_file);
            $duration_seconds = $video_file['playtime_seconds'];
            $duration = date('H:i:s', $duration_seconds);

            $padcast = Padcast::find($id);

            if (!File::exists($pathFile)) {
                File::makeDirectory($pathFile, 0777, true);
            }

            $file->move($pathFile, $fileName);
            $path_file = "uploads/padcast/$fileName";

            if ($request->has('name')) {
                $padcast->name = $request->input('name');
            }
            if ($request->has('text')) {
                $padcast->text = $request->input('text');
            }
            if ($request->has('padcastCategory_id')) {
                $padcast->text = $request->input('text');
            }

            $padcast->time = $duration;
            $padcast->file_path = $path_file;
            $padcast->bulk = $this->formatBytes($sizeFile);

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

    public function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

}
