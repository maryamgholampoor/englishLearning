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
        $request->validate([
            'name' => 'required|string|max:255|unique:padcast_category,name',
            'description' => 'required|string',
            'view_count' => 'integer|min:0',
            'image_path' => 'required|string',
            'type' => 'required|in:text,voice',
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            // Create a new Padcast Category
            $padcastCategory = new PadcastCategory();
            $padcastCategory->name = $request->input('name');
            $padcastCategory->description = $request->input('description');
            $padcastCategory->view_count = $request->input('view_count', 0); // Default to 0 if not provided
            $padcastCategory->image_path = $request->input('image_path');
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

    public function showPadcastCategory($id)
    {
        try {
            // Find the Padcast Category by ID
            $padcastCategory = PadcastCategory::findOrFail($id);

            // Return a success response with the category data
            return response()->json([
                'message' => 'Category retrieved successfully',
                'data' => $padcastCategory
            ], 200);

        } catch (\Exception $exception) {
            // Return an error response if the category is not found
            return response()->json([
                'message' => 'Category not found',
                'error' => $exception->getMessage()
            ], 404);
        }
    }

    public function updatePadcastCategory(Request $request, $id)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:padcast_category,name,' . $id,
            'description' => 'sometimes|required|string',
            'view_count' => 'sometimes|integer|min:0',
            'image_path' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:text,voice',
        ]);

        try {
            // Find the Padcast Category by ID
            $padcastCategory = PadcastCategory::findOrFail($id);

            // Update the category with the new data
            $padcastCategory->name = $request->input('name', $padcastCategory->name);
            $padcastCategory->description = $request->input('description', $padcastCategory->description);
            $padcastCategory->view_count = $request->input('view_count', $padcastCategory->view_count);
            $padcastCategory->image_path = $request->input('image_path', $padcastCategory->image_path);
            $padcastCategory->type = $request->input('type', $padcastCategory->type);
            $padcastCategory->save();

            // Return a success response with the updated category data
            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $padcastCategory
            ], 200);

        } catch (\Exception $exception) {
            // Return an error response if the update fails
            return response()->json([
                'message' => 'Failed to update category',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function deletePadcastCategory($id)
    {
        try {
            // Find the Padcast Category by ID
            $padcastCategory = PadcastCategory::findOrFail($id);

            // Soft delete the category
            $padcastCategory->delete();

            // Return a success response
            return response()->json([
                'message' => 'Category deleted successfully'
            ], 200);

        } catch (\Exception $exception) {
            // Return an error response if the deletion fails or the category is not found
            return response()->json([
                'message' => 'Failed to delete category',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function addPadcast(Request $request)
    {
        // Validate the request input
        $request->validate([
            'name' => 'required|string|max:255',
            'file_path' => 'required|string',
            'time' => 'required|date_format:H:i:s',
            'bulk' => 'required|string|max:255',
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->store('padcasts', 'public'); // Store the file in 'storage/app/public/padcasts'
            }

            // Create a new Padcast
            $padcast = new Padcast();
            $padcast->name = $request->input('name');
            $padcast->file_path = $filePath;
            $padcast->time = $request->input('time');
            $padcast->bulk = $request->input('bulk');
            $padcast->save();

            // Commit the transaction
            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'Padcast created successfully',
                'data' => $padcast
            ], 201);

        } catch (\Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create padcast',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function showPadcast()
    {
        try {
            // Find the Padcast by ID
            $padcast = Padcast::get();

            // Return a success response with the padcast data
            return response()->json([
                'message' => 'Padcast retrieved successfully',
                'data' => $padcast
            ], 200);

        } catch (\Exception $exception) {
            // Return an error response if the padcast is not found or another error occurs
            return response()->json([
                'message' => 'Failed to retrieve padcast',
                'error' => $exception->getMessage()
            ], 404);
        }
    }

    public function updatePadcast(Request $request, $id)
    {
        // Validate the request input
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'file' => 'sometimes|required|file|mimes:mp3,wav',
            'time' => 'sometimes|required|date_format:H:i:s',
            'bulk' => 'sometimes|required|string|max:255',
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();

            // Find the Padcast by ID
            $padcast = Padcast::findOrFail($id);

            // Handle the file upload if a new file is provided
            if ($request->hasFile('file')) {
                // Delete the old file if it exists
                if ($padcast->file_path) {
                    Storage::disk('public')->delete($padcast->file_path);
                }

                // Store the new file
                $file = $request->file('file');
                $filePath = $file->store('padcasts', 'public');
                $padcast->file_path = $filePath;
            }

            // Update the padcast with new data
            if ($request->has('name')) {
                $padcast->name = $request->input('name');
            }
            if ($request->has('time')) {
                $padcast->time = $request->input('time');
            }
            if ($request->has('bulk')) {
                $padcast->bulk = $request->input('bulk');
            }

            // Save the updated padcast
            $padcast->save();

            // Commit the transaction
            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'Padcast updated successfully',
                'data' => $padcast
            ], 200);

        } catch (\Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update padcast',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function deletePadcast($id)
    {
        try {

            DB::beginTransaction();
            $padcast = Padcast::findOrFail($id);
            if ($padcast->file_path) {
                Storage::disk('public')->delete($padcast->file_path);
            }

            $padcast->delete();

            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'Padcast deleted successfully',
            ], 200);

        } catch (\Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete padcast',
                'error' => $exception->getMessage()
            ], 500);
        }
    }



}
