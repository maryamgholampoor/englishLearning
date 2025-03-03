<?php

namespace App\Http\Controllers\v1;

use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Http\Utilities\StatusCode;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookSeason;
use App\Models\Music;
use App\Models\Subscription;
use App\Models\Word;
use App\Models\WordCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Padcast;
use App\Models\PadcastCategory;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
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
            'image' => 'file',
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
                $path_file = "uploads/padcastCat/$fileName";
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
            'name' => ['required', 'string', 'max:255'],
            'file' => ['file', 'mimes:mp3,wav,aac,ogg,flac,wma,m4a', 'max:10240'], // Max 10MB file, only audio formats
            'padcastCategory_id' => ['required', 'integer', 'exists:padcast_category,id'],
            'text' => ['string']
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            $padcastCategory_id = $request->input('padcastCategory_id');
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $pathFile = app()->basePath('public/uploads/padcast' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('file')) {
                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
                $file->move($pathFile, $fileName);
                $path_file = "uploads/padcast/$fileName";
            }

            $sizeFile = File::size($path_file);

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
            $padcast->padcastCategory_id = $padcastCategory_id;
            $padcast->save();

            $podcasts = Padcast::where('id', $padcast->id)->with('padcastCategory')->first();

            // Commit the transaction
            DB::commit();

            return $this->sendJsonResponse($podcasts, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showPadcast(Request $request)
    {
        $this->validate($request, [
            'integer, exists:padcast_category,id',
        ]);

        $category_id = $request->input('category_id');

        try {
            if (isset($category_id)) {
                $padcast = Padcast::with('padcastCategory')->where('padcastCategory_id', $category_id)->get();
            } else {
                $padcast = Padcast::with('padcastCategory')->get();
            }
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
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'file' => ['file', 'max:10240'], // Max 10MB file, only audio formats
            'padcastCategory_id' => ['required', 'integer', 'exists:padcast_category,id'],
            'text' => ['string']
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $pathFile = app()->basePath('public/uploads/padcast' . DIRECTORY_SEPARATOR);

                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
                $file->move($pathFile, $fileName);
                $path_file = "uploads/padcast/$fileName";


                $sizeFile = File::size($path_file);

                $getID3 = new \getID3;
                $video_file = $getID3->analyze($path_file);
                $duration_seconds = $video_file['playtime_seconds'];
                $duration = date('H:i:s', $duration_seconds);

                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
            }

            $padcast = Padcast::find($id);

            $duration=null;
            $sizeFile=null;

            if ($request->has('name')) {

                $padcast->name = $request->input('name');
            }
            if ($request->has('text')) {
                $padcast->text = $request->input('text');
            }
            if ($request->has('padcastCategory_id')) {
                $padcast->padcastCategory_id = $request->input('padcastCategory_id');
            }
            if ($request->has('file')) {
                $padcast->file_path = $path_file;
            }
            if($duration != null){

                $padcast->time = $duration;
            }
            if($sizeFile != null){

                $padcast->bulk = $this->formatBytes($sizeFile);
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

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    public function multiDelete(Request $request)
    {
        $this->validate($request, [
            'type' => [
                'required',
                'string',
                Rule::in([
                    'padcast', 'music', 'wordCategory', 'word', 'subscription',
                    'bookCategory', 'book', 'BookSeason'
                ]),
            ],
            'id' => [
                'required',
                'array',
                'min:1',
            ],
        ]);

        try {
            DB::beginTransaction();
            $type = $request->type;
            $id = $request->id; // Expecting an array of IDs

            if (!is_array($id) || empty($id)) {
                return response()->json(['error' => 'IDs must be a non-empty array'], 400);
            }

            if ($type === "padcast") {
                Padcast::whereIn('id', $id)->delete();
            } elseif ($type === "music") {
                Music::whereIn('id', $id)->delete();
            } elseif ($type === "wordCategory") {
                WordCategory::whereIn('id', $id)->delete();
            } elseif ($type === "word") {
                Word::whereIn('id', $id)->delete();
            } elseif ($type === "subscription") {
                Subscription::whereIn('id', $id)->delete();
            } elseif ($type === "bookCategory") {
                BookCategory::whereIn('id', $id)->delete();
            } elseif ($type === "book") {
                Book::whereIn('id', $id)->delete();
            } elseif ($type === "BookSeason") {
                BookSeason::whereIn('id', $id)->delete();
            }

            DB::commit();

            return $this->sendJsonResponse(null, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

}
