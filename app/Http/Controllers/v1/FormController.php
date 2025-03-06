<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Models\Book;
use App\Models\Bookmark;
use App\Models\BookSeason;
use App\Models\Form;
use App\Models\Padcast;
use App\Models\Question;
use App\Models\SubscriptionFeature;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\BookCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;
use function PHPUnit\Framework\isEmpty;

class FormController extends Controller
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

    public function addForm(Request $request)
    {
        $this->validate($request, [
            'category_id' => 'required|integer|exists:book_category,id',
            'season_id' => 'required|integer|exists:book_season,id',
            'book_id' => 'required|integer|exists:book,id',
        ]);
        try {
            // Start a transaction
            DB::beginTransaction();
            $form = new Form();
            $form->category_id = $request->category_id;
            $form->season_id = $request->season_id;
            $form->book_id = $request->book_id;
            $form->save();


            DB::commit();

            return $this->sendJsonResponse($form, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function editForm(Request $request, $id)
    {
        $this->validate($request, [
            'category_id' => 'required|integer|exists:book_category,id',
            'season_id' => 'required|integer|exists:book_season,id',
            'book_id' => 'required|integer|exists:book,id',
        ]);
        try {
            // Start a transaction
            DB::beginTransaction();

            $form = Form::where('id', $id)->first();
            $form->category_id = $request->category_id;
            $form->season_id = $request->season_id;
            $form->book_id = $request->book_id;
            $form->save();


            DB::commit();

            return $this->sendJsonResponse($form, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showForm()
    {
        try {
            DB::beginTransaction();

            $form = Form::with('bookCategory', 'bookSeason', 'book', 'question')->get();

            DB::commit();
            return $this->sendJsonResponse($form, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteForm($id)
    {
        try {
            DB::beginTransaction();
            $form = Form::where('id', $id)->delete();
            DB::commit();

            return $this->sendJsonResponse($form, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function addQuestion(Request $request)
    {
        $this->validate($request, [
            'form_id' => 'required|integer|exists:form,id',
            'category_id' => 'required|integer|exists:book_category,id',
            'question' => 'required|string',
            'question_type' => 'required|integer',
            'option_one' => 'string',
            'option_two' => 'string',
            'option_three' => 'string',
            'option_four' => 'string',
            'start_range' => 'string',
            'end_range' => 'string',
        ]);
        try {
            // Start a transaction
            DB::beginTransaction();

            $Question = new Question();
            $Question->form_id = $request->form_id;
            $Question->question = $request->question;
            $Question->question_type = $request->question_type;

            if ($request->question_type == 1) {
                $Question->option_one = $request->option_one;
                $Question->option_two = $request->option_two;
                $Question->option_three = $request->option_three;
                $Question->option_four = $request->option_four;
            } else if ($request->question_type == 2) {
                $Question->option_one = $request->option_one;
                $Question->option_two = $request->option_two;
            } else if ($request->question_type == 3) {
                $Question->start_range = $request->start_range;
                $Question->end_range = $request->end_range;
            }
            $Question->save();

            DB::commit();

            return $this->sendJsonResponse($Question, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function editQuestion(Request $request, $id)
    {
        $this->validate($request, [
            'form_id' => 'required|integer|exists:form,id',
            'category_id' => 'required|integer|exists:book_category,id',
            'question' => 'required|string',
            'question_type' => 'required|integer',
            'option_one' => 'string',
            'option_two' => 'string',
            'option_three' => 'string',
            'option_four' => 'string',
            'start_range' => 'string',
            'end_range' => 'string',
        ]);
        try {
            // Start a transaction
            DB::beginTransaction();

            $Question = Question::where('id', $id)->first();
            $Question->form_id = $request->form_id;
            $Question->question = $request->question;
            $Question->question_type = $request->question_type;

            if ($request->question_type == 1) {
                $Question->option_one = $request->option_one;
                $Question->option_two = $request->option_two;
                $Question->option_three = $request->option_three;
                $Question->option_four = $request->option_four;
            } else if ($request->question_type == 2) {
                $Question->option_one = $request->option_one;
                $Question->option_two = $request->option_two;
            } else if ($request->question_type == 3) {
                $Question->start_range = $request->start_range;
                $Question->end_range = $request->end_range;
            }
            $Question->save();

            DB::commit();

            return $this->sendJsonResponse($Question, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showQuestion($id)
    {
        try {
            $question = Question::where('id', $id)->first();
            DB::commit();

            return $this->sendJsonResponse($question, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteQuestion($id){
        try {
            $question = Question::where('id', $id)->delete();
            DB::commit();

            return $this->sendJsonResponse($question, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showQuestionForm($id){
        try {
            $question = Question::where('form_id',$id)->get();
            DB::commit();

            return $this->sendJsonResponse($question, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showQuestionBookSeason(Request $request){
        $this->validate($request, [
            'season_id' => 'required|integer|exists:book_season,id',
            'book_id' => 'required|integer|exists:book,id',
        ]);

        try {
            $season_id=$request->season_id;
            $book_id=$request->book_id;

            $question = Form::where('season_id',$season_id)->where('season_id',$book_id)->with('question')->get();
            DB::commit();

            return $this->sendJsonResponse($question, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }



}

