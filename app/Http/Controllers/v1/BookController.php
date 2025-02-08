<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Models\Book;
use App\Models\Bookmark;
use App\Models\BookSeason;
use App\Models\Padcast;
use App\Models\SubscriptionFeature;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\BookCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;
use function PHPUnit\Framework\isEmpty;

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

            $bookCategory = BookCategory::find($id);
            $bookCategory->name = $request->name;
            $bookCategory->save();

            DB::commit();

            return $this->sendJsonResponse($bookCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showBookCategory()
    {
        try {
            $bookCategory = BookCategory::all();

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
        $accent = $request->accent;
        $author = $request->author;
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
            $book->author = $author;
            $book->accent = $accent;
            $book->name = $name;
            $book->image_path = $path_file;
            $book->save();
            DB::commit();

            return $this->sendJsonResponse($book, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }


    }

    public function editBook(Request $request, $id)
    {
        $book_category_id = $request->book_category_id;
        $name = $request->name;
        $accent = $request->accent;
        $author = $request->author;
        $image = $request->image;

        try {

            $book = Book::findOrFail($id);

            $book->book_category_id = $book_category_id;
            $book->accent = $accent;
            $book->author = $author;
            $book->name = $name;
            if ($request->hasFile('image')) {
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
            $book = Book::with('bookCategory')->get();
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
            $bookCategory_id = $request->bookCategory_id;
            $book = Book::where('book_category_id', $bookCategory_id)->get();
            return $this->sendJsonResponse($book, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
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
        $season_name = $request->season_name;

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
            $bookSeason->season_name = $season_name;
            $bookSeason->save();
            DB::commit();

            return $this->sendJsonResponse($bookSeason, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }

    public function editBookSeason(Request $request, $id)
    {

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

        } catch (\Exception $exception) {
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
            $id = $request->book_id;
            $BookSeason = BookSeason::with('book')->where('book_id', $id)->get();
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

    public function addBookmark(Request $request)
    {
        $book_id = $request->book_id;
        $user_id = $request->user_id;

        $this->validate($request, [
            'user_id' => 'required|integer',
            'book_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $bookmark = Bookmark::where('user_id', $user_id)->where('book_id', $book_id)->first();

            if (empty($bookmark)) {
                $bookmark = new Bookmark();
                $bookmark->user_id = $user_id;
                $bookmark->book_id = $book_id;
                $bookmark->save();

                DB::commit();
                return $this->sendJsonResponse($bookmark, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

            } else {
                DB::commit();
                return $this->sendJsonResponse([], 'this bookmark is exist', $this->getStatusCodeByCodeName('OK'));
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function deleteBookmark(Request $request)
    {
        $book_id = $request->book_id;
        $user_id = $request->user_id;

        $this->validate($request, [
            'user_id' => 'required|integer',
            'book_id' => 'required|integer',
        ]);

        try {

            DB::beginTransaction();

            $bookmark = Bookmark::where('book_id', $book_id)->where('user_id', $user_id)->delete();

            DB::commit();
            if ($bookmark) {
                return $this->sendJsonResponse($bookmark, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }

    public function showBookmark(Request $request, $user_id)
    {
        try {
            DB::beginTransaction();

            $bookmark = Bookmark::with('user')->where('user_id', $user_id)->get();

            DB::commit();
            return $this->sendJsonResponse($bookmark, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }

    public function multiDelete(Request $request)
    {
        $type = $request->type;
        $book_id = $request->book_id;
        $pudcast_id = $request->book_id;

        try {
            DB::beginTransaction();

            if ($type == "pudcast") {

                foreach ($pudcast_id as $key=>$id)
                {
                    $pudcast=Padcast::where('id',$id)->delete();
                }

                DB::commit();
                return $this->sendJsonResponse($pudcast, trans('message.delete_successfully'), $this->getStatusCodeByCodeName('OK'));
            }

            else if ($type == "book") {

                foreach ($book_id as $key=>$id)
                {
                    $book=Book::where('id',$id)->delete();
                }

                DB::commit();
                return $this->sendJsonResponse($book, trans('message.delete_successfully'), $this->getStatusCodeByCodeName('OK'));
            }

        }
        catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

}

