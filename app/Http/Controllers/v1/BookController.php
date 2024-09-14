<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Models\Book;
use App\Models\BookSeason;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\BookCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;

class BookController extends Controller
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

    public function addBookCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);
        try {
            // Start a transaction
            DB::beginTransaction();
            $bookCategory = new BookCategory();
            $bookCategory->name = $request->name;
            $bookCategory->save();
            DB::commit();

            return $this->sendJsonResponse($bookCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function editBookCategory(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);
        try {
            DB::beginTransaction();
            $bookCategory = BookCategory::findOrFail($id);
            $bookCategory->name = $request->name;
            $bookCategory->save();
            DB::commit();

            return $this->sendJsonResponse($bookCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showBookCategory($id)
    {
        try {
            $bookCategory = BookCategory::findOrFail($id);

            return $this->sendJsonResponse($bookCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteBookCategory($id)
    {
        try {
            $bookCategory = BookCategory::findOrFail($id);
            $bookCategory->delete();

            return $this->sendJsonResponse($bookCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }


    public function addBook(Request $request)
    {

        $book_category_id = $request->book_category_id;
        $name = $request->name;
        $image = $request->image;

        try {
            $fileName = $image->getClientOriginalName();
            $path = app()->basePath('public/uploads/book' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('image')) {

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }

                $image->move($path, $fileName);

                $path_file = "uploads/book/$fileName";
            }

            DB::beginTransaction();
            $book = new Book();
            $book->book_category_id = $book_category_id;
            $book->name = $name;
            $book->image_path = $path_file;
            $book->save();
            DB::commit();

            return $this->sendJsonResponse($book, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }


    }

    public function editBook(Request $request,$id){

        $book_category_id = $request->book_category_id;
        $name = $request->name;
        $image = $request->image;

        try {

            $book = Book::findOrFail($id);

            $book->book_category_id = $book_category_id;
            $book->name = $name;
            if ($request->hasFile('image'))
            {
                $fileName = $image->getClientOriginalName();
                $path = app()->basePath('public/uploads/book' . DIRECTORY_SEPARATOR);

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }

                $image->move($path, $fileName);
                $path_file = "uploads/book/$fileName";

                $book->image_path = $path_file;
            }

            DB::beginTransaction();
            $book->save();
            DB::commit();

            return $this->sendJsonResponse($book, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showBook(Request $request)
    {
        try {
            DB::beginTransaction();
            $book = Book::get();
            DB::commit();

            return $this->sendJsonResponse($book, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }

    public function deleteBook($id)
    {
        try {
            $book = Book::findOrFail($id);
            $book->delete();

            return $this->sendJsonResponse($book, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showBookWithCategory(Request $request)
    {
        try {
            $bookCategory_id=$request->bookCategory_id;
            $book=Book::where('book_category_id',$bookCategory_id)->get();
            return $this->sendJsonResponse($book, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        }
        catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }


    public function addBookSeason(Request $request)
    {
        $book_id = $request->book_id;
        $title_fa = $request->title_fa;
        $title_en = $request->title_en;
        $image = $request->image;
        $text = $request->text;

        try {
            $fileName = $image->getClientOriginalName();
            $path = app()->basePath('public/uploads/book' . DIRECTORY_SEPARATOR);

            if ($request->hasFile('image')) {

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }

                $image->move($path, $fileName);

                $path_file = "uploads/book/$fileName";
            }

            DB::beginTransaction();
            $bookSeason = new BookSeason();
            $bookSeason->title_fa = $title_fa;
            $bookSeason->title_en = $title_en;
            $bookSeason->image = $path_file;
            $bookSeason->text = $text;
            $bookSeason->book_id = $book_id;
            $bookSeason->save();
            DB::commit();

            return $this->sendJsonResponse($bookSeason, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }

    public function editBookSeason(Request $request,$id){

        $book_id = $request->book_id;
        $title_fa = $request->title_fa;
        $title_en = $request->title_en;
        $image = $request->image;
        $text = $request->text;

        try {

            if ($request->hasFile('image')) {

                $fileName = $image->getClientOriginalName();
                $path = app()->basePath('public/uploads/book' . DIRECTORY_SEPARATOR);

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }

                $image->move($path, $fileName);

                $path_file = "uploads/book/$fileName";
            }

            DB::beginTransaction();
            $bookSeason = BookSeason::find($id);
            $bookSeason->title_fa = $title_fa;
            $bookSeason->title_en = $title_en;
            $bookSeason->image = $path_file;
            $bookSeason->text = $text;
            $bookSeason->book_id = $book_id;
            $bookSeason->save();
            DB::commit();

            return $this->sendJsonResponse($bookSeason, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showAllBookSeason(Request $request)
    {
        try {
            DB::beginTransaction();
            $BookSeason = BookSeason::get();
            DB::commit();

            return $this->sendJsonResponse($BookSeason, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showBookSeason(Request $request)
    {
        try {
            DB::beginTransaction();
            $id=$request->book_id;
            $BookSeason = BookSeason::where('book_id',$id)->get();
            DB::commit();

            return $this->sendJsonResponse($BookSeason, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteBookSeason($id)
    {
        try {
            $bookSeason = BookSeason::findOrFail($id);
            $bookSeason->delete();

            return $this->sendJsonResponse($bookSeason, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }


}
