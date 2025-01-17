<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Http\Utilities\StatusCode;
use App\Models\App;
use App\Models\Padcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UploadApplication extends Controller
{
    use Response, StatusCode, UtilityRequest;

    public function uploadApplication(Request $request)
    {
        // Validate the request input

        try {
            DB::beginTransaction();
            $App=new App();
            if ($request->hasFile('file'))
            {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $path = app()->basePath('public/uploads/app' . DIRECTORY_SEPARATOR);

                if ($request->hasFile('file')) {
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0777, true);
                    }
                    $file->move($path, $fileName);
                    $path_file = "uploads/padcast/$fileName";
                    $App->path = $path_file;
                }
            }
            $App->save();

            DB::commit();

            return $this->sendJsonResponse($App, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

}
