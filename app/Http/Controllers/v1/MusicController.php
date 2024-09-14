<?php

namespace App\Http\Controllers;

use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Http\Utilities\StatusCode;
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

    public function showMusicCategory()
    {
        try {
            // Find the MusicCategory by ID
            $musicCategory = MusicCategory::get();

            // Return a success response with the music category data
            return response()->json([
                'message' => 'Music category retrieved successfully',
                'data' => $musicCategory
            ], 200);

        } catch (\Exception $exception) {
            // Return an error response if the music category is not found or another error occurs
            return response()->json([
                'message' => 'Failed to retrieve music category',
                'error' => $exception->getMessage()
            ], 404);
        }
    }

    public function storeMusic(Request $request)
    {
        // Validate the request input
        $request->validate([
            'path' => 'required|file|mimes:mp3,wav',
            'text' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'music_category_id' => 'required|integer|exists:music_category,id',
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            // Handle the file upload for the music path
            $pathFile = $request->file('path');
            $pathFilePath = $pathFile->store('music_files', 'public');

            // Handle the file upload for the image
            $imageFile = $request->file('image');
            $imagePath = $imageFile->store('music_images', 'public');

            // Create a new Music record
            $music = new Music();
            $music->path = $pathFilePath;
            $music->text = $request->input('text');
            $music->image_path = $imagePath;
            $music->music_category_id = $request->input('music_category_id');
            $music->save();

            // Commit the transaction
            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'Music created successfully',
                'data' => $music
            ], 201);

        } catch (\Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create music',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function deleteMusic($id)
    {
        try {
            // Start a transaction
            DB::beginTransaction();

            $music = Music::findOrFail($id);

            if ($music->path) {
                Storage::disk('public')->delete($music->path);
            }

            if ($music->image_path) {
                Storage::disk('public')->delete($music->image_path);
            }

            $music->delete();

            // Commit the transaction
            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'Music deleted successfully',
            ], 200);

        } catch (\Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete music',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function showMusic($id)
    {
        try {
            // Find the Music by ID
            $music = Music::findOrFail($id);

            // Return a success response with the music data
            return response()->json([
                'message' => 'Music retrieved successfully',
                'data' => $music
            ], 200);

        } catch (\Exception $exception) {
            // Return an error response if the music is not found or another error occurs
            return response()->json([
                'message' => 'Failed to retrieve music',
                'error' => $exception->getMessage()
            ], 404);
        }
    }

}
