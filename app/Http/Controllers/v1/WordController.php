<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Http\Utilities\StatusCode;
use App\Models\Word;
use App\Models\WordCategory;
use App\Models\WordUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class WordController extends Controller
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

    public function storeWordCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'word_count' => 'required|string'
        ]);
        try {
            // Start a transaction
            DB::beginTransaction();
            $wordCategory = new WordCategory();
            $wordCategory->name = $request->name;
            $wordCategory->word_count = $request->word_count;
            $wordCategory->save();
            DB::commit();

            return $this->sendJsonResponse($wordCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function updateWordCategory(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required|string',
            'word_count' => 'required|string'
        ]);
        try {
            // Start a transaction
            DB::beginTransaction();
            $wordCategory = WordCategory::find($id);
            $wordCategory->name = $request->name;
            $wordCategory->word_count = $request->word_count;
            $wordCategory->save();
            DB::commit();

            return $this->sendJsonResponse($wordCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteWordCategory(Request $request, $id)
    {

        try {
            // Start a transaction
            DB::beginTransaction();
            $wordCategory = WordCategory::where('id', $id)->delete();
            DB::commit();

            return $this->sendJsonResponse($wordCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showWordCategory(Request $request)
    {
        $word_category_id = $request->word_category_id;
        $user_id = $request->user_id;

        $this->validate($request, [
            'word_category_id' => 'required|integer',
            'user_id'=>'required|integer'
        ]);

        try {
            $wordCategory = WordCategory::get();
            $wordCollect=Collect();
            foreach ($wordCategory as $category)
            {
                $category_id=$category->id;
                $wordUser=WordUser::where('user_id',$user_id)->where('category_id',$category_id)->count();
                $word=Word::where('word_category_id',$word_category_id)->count();

                $category->setAttribute('saving_count',$wordUser);
                $category->setAttribute('word_counts',$word);
            }
            return $this->sendJsonResponse($wordCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function storeUserWord(Request $request)
    {
      $user_id=$request->user_id;
      $word_id=$request->word_id;
      $category_id=$request->category_id;

        $this->validate($request, [
            'user_id' => 'required|integer',
            'word_id' => 'required|integer',
            'category_id' => 'required|integer'
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();
            $wordUser = WordUser::where('user_id',$user_id)->where('word_id',$word_id)->where('category_id',$category_id)->first();
            if(!$wordUser)
            {
                $wordUser = new WordUser();
                $wordUser->user_id = $user_id;
                $wordUser->word_id = $word_id;
                $wordUser->category_id = $category_id;
                $wordUser->save();
            }else
            {
                return $this->sendJsonResponse($wordUser,"exist", $this->getStatusCodeByCodeName('OK'));
            }

            DB::commit();

            return $this->sendJsonResponse($wordUser, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showCategoryWord(Request $request)
    {
        $word_category_id = $request->word_category_id;

        $this->validate($request, [
            'word_category_id' => 'required|string',
        ]);

        try {
            $wordCategory = Word::where('word_category_id',$word_category_id)->get();
            foreach ($wordCategory as $word)
            {
                $wordUser = WordUser::where('word_id',$word->id)->first();
                if($wordUser){
                    $word->setAttribute('is_saved',"true");
                }
                else{
                    $word->setAttribute('is_saved',"false");
                }
            }

            return $this->sendJsonResponse($wordCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showVoice(Request $request)
    {
        $word_id = $request->word_id;

        $this->validate($request, [
            'word_id' => 'required',
        ]);

        try {
            $word = Word::where('id',$word_id)->select("music_path")->first();

            return $this->sendJsonResponse($word, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

}
