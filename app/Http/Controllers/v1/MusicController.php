<?php

namespace App\Http\Controllers\v1;

use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Http\Utilities\StatusCode;
use App\Models\PadcastCategory;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Music;
use App\Models\MusicCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MusicController extends Controller
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

    public function addMusicCategory(Request $request)
    {
        // Validate the request input
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:music_category,name',
            'image' => 'file',
        ]);

        try {
            $image = $request->file('image');
            $fileName = $image->getClientOriginalName();
            $path = app()->basePath('public/uploads/musicCat' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('image')) {
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }
                $image->move($path, $fileName);
                $path_file = "uploads/musicCat/$fileName";
            }
            // Start a transaction
            DB::beginTransaction();

            // Create a new Padcast Category
            $musicCategory = new MusicCategory();
            $musicCategory->name = $request->input('name');
            $musicCategory->image_path = $path_file;
            $musicCategory->save();

            // Commit the transaction
            DB::commit();

            return $this->sendJsonResponse($musicCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showMusicCategory()
    {
        try {
            // Find the Padcast Category by ID
            $musicCategory = MusicCategory::get();
            DB::commit();
            return $this->sendJsonResponse($musicCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function updateMusicCategory(Request $request, $id)
    {
        // Validate the incoming request data
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:music_category,name',
            ]);

        try {
            $image = $request->file('image');
            if (isset($image)) {
                $image = $request->file('image');
                $fileName = $image->getClientOriginalName();
                $path = app()->basePath('public/uploads/musicCat' . DIRECTORY_SEPARATOR);

                if ($request->hasFile('image')) {
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0777, true);
                    }
                    $image->move($path, $fileName);
                    $path_file = "uploads/musicCat/$fileName";
                }
            }

            // Find the Padcast Category by ID
            $musicCategory = MusicCategory::findOrFail($id);

            // Update the category with the new data
            $musicCategory->name = $request->input('name', $musicCategory->name);
            $musicCategory->image_path = $path_file;
            $musicCategory->save();
            DB::commit();

            // Return a success response with the updated category data
            return $this->sendJsonResponse($musicCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteMusicCategory($id)
    {
        try {
            $musicCategory = MusicCategory::find($id);

            if ($musicCategory) {

                $musicCategory->delete();
            } else {
                return $this->sendJsonResponse([], trans('Not Found'), $this->getStatusCodeByCodeName('OK'));
            }

            DB::commit();

            return $this->sendJsonResponse($musicCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }


    public function addMusic(Request $request)
    {
        // Validate the request input
        $this->validate($request, [
            'name' => ['required', 'string'],
            'text' => ['required', 'string'],
            'file' => ['file', 'mimes:mp3,wav,aac,ogg,flac,wma,m4a', 'max:10240'], // Max 10MB file, only audio formats
            'image' => ['file'], // Max 10MB file, only audio formats
            'singer' => ['string'], // Max 10MB file, only audio formats
            'musicCategory_id' => ['required', 'integer', 'exists:music_category,id'],
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            $musicCategory_id = $request->input('musicCategory_id');
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $pathFile = app()->basePath('public/uploads/music' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('file')) {
                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
                $file->move($pathFile, $fileName);
                $path_file = "uploads/music/$fileName";
            }

            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $pathImage = app()->basePath('public/uploads/music' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('file')) {
                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
                $file->move($pathImage, $imageName);
                $path_image = "uploads/music/$imageName";
            }

            // Create a new Padcast
            $music = new Music();
            $music->name = $request->input('name');
            $music->text = $request->input('text');
            $music->image = $path_image;
            $music->file_path = $path_file;
            $music->musicCategory_id = $musicCategory_id;
            $music->save();

            $music = Music::where('id', $music->id)->with('musicCategory')->first();

            // Commit the transaction
            DB::commit();

            return $this->sendJsonResponse($music, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showMusic(Request $request)
    {

        $category_id = $request->input('category_id');

        try {
            if (isset($category_id)) {
                $music = Music::with('musicCategory')->where('music_category_id', $category_id)->get();
            } else {
                $music = Music::with('musicCategory')->get();
            }
            DB::commit();

            return $this->sendJsonResponse($music, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function updateMusic(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required', 'string'],
            'text' => ['required', 'string'],
            'file' => ['file', 'mimes:mp3,wav,aac,ogg,flac,wma,m4a', 'max:10240'], // Max 10MB file, only audio formats
            'image' => ['file'], // Max 10MB file, only audio formats
            'singer' => ['string'], // Max 10MB file, only audio formats
            'musicCategory_id' => ['required', 'integer', 'exists:music_category,id'],
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            $musicCategory_id = $request->input('musicCategory_id');
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $pathFile = app()->basePath('public/uploads/music' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('file')) {
                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
                $file->move($pathFile, $fileName);
                $path_file = "uploads/music/$fileName";
            }

            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $pathImage = app()->basePath('public/uploads/music' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('file')) {
                if (!File::exists($pathFile)) {
                    File::makeDirectory($pathFile, 0777, true);
                }
                $file->move($pathImage, $imageName);
                $path_image = "uploads/music/$imageName";
            }

            // Create a new Padcast
            $music = Music::find($id);
            $music->name = $request->input('name');
            $music->text = $request->input('text');
            $music->image = $path_image;
            $music->file_path = $path_file;
            $music->musicCategory_id = $musicCategory_id;
            $music->save();

            $music = Music::where('id', $music->id)->with('musicCategory')->first();

            // Commit the transaction
            DB::commit();

            return $this->sendJsonResponse($music, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteMusic($id)
    {
        try {
            DB::beginTransaction();
            $music = Music::find($id);

            if ($music) {
                $music->delete();
            } else {
                return $this->sendJsonResponse([], trans('Not Found'), $this->getStatusCodeByCodeName('OK'));
            }
            DB::commit();
            return $this->sendJsonResponse($music, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }


    public function showMusicWithCategory($id)
    {
        try {
            $Music = Music::where('music_category_id',$id)->get();
            DB::commit();
            return $this->sendJsonResponse($Music, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

}
