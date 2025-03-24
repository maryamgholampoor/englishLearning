<?php

use App\Http\Controllers\v1\LoginController;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'v1'], function () use ($router) {
    $router->group(['namespace' => $path_info[1] ?? 'v1'], function () use ($router) {

        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('login', ['as' => 'login', 'uses' => "LoginController@doLogin"]);
            $router->post('login/code', ['as' => 'login.code', 'uses' => "LoginController@checkLoginCode"]);
        });

        $router->group(['prefix' => 'padcast'], function () use ($router) {
            $router->post('padcastCategory', ['as' => 'padcast.add', 'uses' => "PadcastController@addPadcastCategory"]);
            $router->get('padcastCategory', ['as' => 'padcast.show', 'uses' => "PadcastController@showPadcastCategory"]);
            $router->post('updatePadcastCategory/{id}', ['as' => 'padcast.update', 'uses' => "PadcastController@updatePadcastCategory"]);
            $router->delete('deletePadcastCategory/{id}', ['as' => 'padcast.delete', 'uses' => "PadcastController@deletePadcastCategory"]);

            $router->post('addPadcast', ['as' => 'padcast.add', 'uses' => "PadcastController@addPadcast"]);
            $router->post('showPadcast', ['as' => 'padcast.show', 'uses' => "PadcastController@showPadcast"]);
            $router->post('updatePadcast/{id}', ['as' => 'padcast.update', 'uses' => "PadcastController@updatePadcast"]);
            $router->delete('deletePadcast/{id}', ['as' => 'padcast.delete', 'uses' => "PadcastController@deletePadcast"]);
        });

        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('showUsers', ['as' => 'users.show', 'uses' => "UsersController@showAllUsers"]);
            $router->post('showProfile', ['as' => 'users.showProfile', 'uses' => "UsersController@showProfile"]);
            $router->post('editProfile/{id}',['as' => 'users.showProfile', 'uses' => "UsersController@updateProfile"]);
            $router->post('deleteUser', ['as' => 'users.delete', 'uses' => "UsersController@deleteUser"]);
            $router->post('editUsers', ['as' => 'users.edit', 'uses' => "UsersController@editUsers"]);
            $router->post('editProfile', ['as' => 'users.edit', 'uses' => "UsersController@updateProfile"]);
            $router->post('editProfilePicture/{id}', ['as' => 'users.edit', 'uses' => "UsersController@updateProfilePicture"]);

        });

        $router->group(['prefix' => 'subscription'], function () use ($router) {
            $router->post('addSubscription', ['as' => 'subscription.add', 'uses' => "SubscriptionController@addSubscription"]);
            $router->put('editSubscription/{id}', ['as' => 'subscription.edit', 'uses' => "SubscriptionController@editSubscription"]);
            $router->post('deleteSubscription', ['as' => 'subscription.delete', 'uses' => "SubscriptionController@deleteSubscription"]);
            $router->post('showSubscription', ['as' => 'subscription.show', 'uses' => "SubscriptionController@showSubscription"]);
            $router->post('showFeatureSubscription', ['as' => 'subscriptionFeature.show', 'uses' => "SubscriptionController@showFeatureSubscription"]);
        });

        $router->group(['prefix' => 'book'], function () use ($router) {
            $router->post('bookCategories', ['as' => 'book.add', 'uses' => 'BookController@addBookCategory']);
            $router->put('bookCategories/{id}', ['as' => 'book.edit', 'uses' => 'BookController@editBookCategory']);
            $router->get('bookCategories', ['as' => 'bookCategories.show', 'uses' => 'BookController@showBookCategory']);
            $router->delete('bookCategories/{id}', ['as' => 'bookCategories.delete', 'uses' => 'BookController@deleteBookCategory']);

            $router->post('book', ['as' => 'book.add', 'uses' => 'BookController@addBook']);
            $router->put('book/{id}', ['as' => 'book.edit', 'uses' => 'BookController@editBook']);
            $router->get('book', ['as' => 'book.show', 'uses' => 'BookController@showBook']);
            $router->delete('book/{id}', ['as' => 'book.delete', 'uses' => 'BookController@deleteBook']);
            $router->post('showBookWithCategory', ['as' => 'book.delete', 'uses' => 'BookController@showBookWithCategory']);
            $router->get('showAllBooks', ['as' => 'book.delete', 'uses' => 'BookController@showAllBooks']);

            $router->post('bookSeason', ['as' => 'bookSeason.add', 'uses' => 'BookController@addBookSeason']);
            $router->post('bookSeason/{id}', ['as' => 'bookSeason.edit', 'uses' => 'BookController@editBookSeason']);
            $router->get('bookAllSeason', ['as' => 'bookSeason.showAll', 'uses' => 'BookController@showAllBookSeason']);
            $router->get('bookSeason', ['as' => 'bookSeason.show', 'uses' => 'BookController@showBookSeason']);
            $router->delete('BookSeason/{id', ['as' => 'bookSeason.delete', 'uses' => 'BookController@deleteBookSeason']);

        });

        $router->group(['prefix' => 'ticket'], function () use ($router) {
            $router->post('addTicket', ['as' => 'ticket.add', 'uses' => 'TicketController@addTicket']);
            $router->post('editTicket/{id}', ['as' => 'ticket.edit', 'uses' => 'TicketController@editTicket']);
            $router->get('showAllTickets', ['as' => 'ticket.show', 'uses' => 'TicketController@showAllTickets']);
            $router->post('showUserTicket', ['as' => 'ticket.showUser', 'uses' => 'TicketController@showUserTicket']);
            $router->post('changeTicketStatus/{id}', ['as' => 'ticket.showUser', 'uses' => 'TicketController@editTicketStatus']);
            $router->post('downloadFile/{id}', ['as' => 'ticket.showUser', 'uses' => 'TicketController@downloadFile']);
            $router->get('showTicketCategory', ['as' => 'ticket.showUser', 'uses' => 'TicketController@showTicketCategory']);
        });

        $router->group(['prefix' => 'admin'], function () use ($router) {
            $router->post('login', ['as' => 'admin.login', 'uses' => 'LoginController@login']);
        });

        $router->group(['prefix' => 'application'], function () use ($router) {
            $router->post('uploadApplication', ['as' => 'ticket.add', 'uses' => 'UploadApplication@uploadApplication']);
        });

        $router->group(['prefix' => 'bookmark'], function () use ($router) {
            $router->post('add', ['as' => 'bookmark.add', 'uses' => 'BookController@addBookmark']);
            $router->post('delete', ['as' => 'bookmark.delete', 'uses' => 'BookController@deleteBookmark']);
            $router->get('show/{user_id}', ['as' => 'bookmark.show', 'uses' => 'BookController@showBookmark']);

        });

        $router->group(['prefix' => 'wordCategory'], function () use ($router) {
            $router->post('add', ['as' => 'wordCategory.add', 'uses' => 'WordController@storeWordCategory']);
            $router->post('update/{id}', ['as' => 'wordCategory.update', 'uses' => 'WordController@updateWordCategory']);
            $router->delete('delete/{id}', ['as' => 'wordCategory.delete', 'uses' => 'WordController@deleteWordCategory']);
            $router->post('show', ['as' => 'wordCategory.show', 'uses' => 'WordController@showWordCategory']);
            $router->get('show', ['as' => 'wordCategory.showAll', 'uses' => 'WordController@showAllWordCategory']);
        });

        $router->group(['prefix' => 'wordUser'], function () use ($router) {
            $router->post('add', ['as' => 'wordUser.add', 'uses' => 'WordController@storeUserWord']);
        });

        $router->group(['prefix' => 'word'], function () use ($router) {
            $router->post('showCategoryWord', ['as' => 'word.CategoryWord', 'uses' => 'WordController@showCategoryWord']);
            $router->post('showVoice', ['as' => 'word.CategoryWord', 'uses' => 'WordController@showVoice']);
        });

        $router->group(['prefix' => 'delete'], function () use ($router) {
            $router->post('listDelete', ['as' => 'List.delete', 'uses' => 'PadcastController@multiDelete']);
        });

        $router->group(['prefix' => 'role'], function () use ($router) {
            $router->get('/', ['as' => 'role.getAll', 'uses' => 'RoleController@getAllRoles']);
            $router->post('addPerm', ['as' => 'role.create', 'uses' => 'RoleController@addRolePerm']);
            $router->post('store', ['as' => 'role.store', 'uses' => 'RoleController@addRole']);
            $router->post('edit/{id}', ['as' => 'role.edit', 'uses' => 'RoleController@editRole']);
            $router->put('update/{id}', ['as' => 'role.update', 'uses' => 'RoleController@update']);
            $router->delete('delete/{id}', ['as' => 'role.delete', 'uses' => 'RoleController@delete']);
        });

        $router->group(['prefix' => 'permAction'], function () use ($router) {
            $router->get('/', ['as' => 'permAction.getAll', 'uses' => 'RoleController@getPermAction']);
        });

        $router->group(['prefix' => 'form'], function () use ($router)
        {
            $router->post('/add', ['as' => 'form.add', 'uses' => 'FormController@addForm']);
            $router->post('/edit/{id}', ['as' => 'form.edit', 'uses' => 'FormController@editForm']);
            $router->get('/show', ['as' => 'form.show', 'uses' => 'FormController@showForm']);
            $router->delete('/delete/{id}', ['as' => 'form.delete', 'uses' => 'FormController@deleteForm']);

            $router->group(['prefix' => 'question'], function () use ($router)
            {
                $router->post('/add', ['as' => 'question.add', 'uses' => 'FormController@addQuestion']);
                $router->post('/edit/{id}', ['as' => 'question.edit', 'uses' => 'FormController@editQuestion']);
                $router->get('/show/{id}', ['as' => 'question.show', 'uses' => 'FormController@showQuestion']);
                $router->delete('/delete/{id}', ['as' => 'question.delete', 'uses' => 'FormController@deleteQuestion']);
                $router->get('/showQuestionForm/{id}', ['as' => 'question.show', 'uses' => 'FormController@showQuestionForm']);
                $router->post('/showQuestionBookSeason/{id}', ['as' => 'question.show', 'uses' => 'FormController@showQuestionBookSeason']);
            });
        });

        $router->group(['prefix' => 'music'], function () use ($router)
        {
            $router->post('/add', ['as' => 'music.add', 'uses' => 'MusicController@addMusic']);
            $router->post('/edit/{id}', ['as' => 'music.edit', 'uses' => 'MusicController@updateMusic']);
            $router->delete('/delete/{id}', ['as' => 'music.delete', 'uses' => 'MusicController@deleteMusic']);
            $router->get('/show', ['as' => 'music.get', 'uses' => 'MusicController@showMusic']);

            $router->group(['prefix' => 'category'], function () use ($router)
            {
                $router->post('/add', ['as' => 'category.add', 'uses' => 'MusicController@addMusicCategory']);
                $router->post('/edit/{id}', ['as' => 'category.edit', 'uses' => 'MusicController@updateMusicCategory']);
                $router->delete('/delete/{id}', ['as' => 'category.delete', 'uses' => 'MusicController@deleteMusicCategory']);
                $router->get('/show', ['as' => 'category.show', 'uses' => 'MusicController@showMusicCategory']);
                $router->get('/showMusic/{id}', ['as' => 'category.showMusic', 'uses' => 'MusicController@showMusicWithCategory']);
            });
        });

    });
});
