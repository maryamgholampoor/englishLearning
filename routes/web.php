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
            $router->post('login/code', ['as' => 'login.code', 'uses' => "LoginController@checkLoginCode"]);
        });

        $router->group(['prefix' => 'padcast'], function () use ($router) {
            $router->post('padcastCategory', ['as' => 'padcast.add', 'uses' => "PadcastController@addPadcastCategory"]);
            $router->get('padcastCategory', ['as' => 'padcast.show', 'uses' => "PadcastController@showPadcastCategory"]);
            $router->post('updatePadcastCategory/{id}', ['as' => 'padcast.update', 'uses' => "PadcastController@updatePadcastCategory"]);
            $router->delete('deletePadcastCategory/{id}', ['as' => 'padcast.delete', 'uses' => "PadcastController@deletePadcastCategory"]);

            $router->post('addPadcast', ['as' => 'padcast.add', 'uses' => "PadcastController@addPadcast"]);
            $router->get('showPadcast', ['as' => 'padcast.show', 'uses' => "PadcastController@showPadcast"]);
            $router->post('updatePadcast/{id}', ['as' => 'padcast.update', 'uses' => "PadcastController@updatePadcast"]);
            $router->delete('deletePadcast/{id}', ['as' => 'padcast.delete', 'uses' => "PadcastController@deletePadcast"]);
        });

        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('showUsers', ['as' => 'users.show', 'uses' => "UsersController@showAllUsers"]);
            $router->post('showProfile', ['as' => 'users.showProfile', 'uses' => "UsersController@showProfile"]);
            $router->post('deleteUser', ['as' => 'users.delete', 'uses' => "UsersController@deleteUser"]);
            $router->post('editUsers', ['as' => 'users.edit', 'uses' => "UsersController@editUsers"]);
        });

        $router->group(['prefix' => 'subscription'], function () use ($router) {
            $router->post('addSubscription', ['as' => 'subscription.add', 'uses' => "SubscriptionController@addSubscription"]);
            $router->put('editSubscription/{id}', ['as' => 'subscription.edit', 'uses' => "SubscriptionController@editSubscription"]);
            $router->post('deleteSubscription', ['as' => 'subscription.delete', 'uses' => "SubscriptionController@deleteSubscription"]);
            $router->post('showSubscription', ['as' => 'subscription.show', 'uses' => "SubscriptionController@showSubscription"]);
            $router->post('showFeatureSubscription', ['as' => 'subscriptionFeature.show', 'uses' => "SubscriptionController@showFeatureSubscription"]);
        });

        $router->group(['prefix' => 'book'], function () use ($router)
        {
            $router->post('bookCategories', ['as' => 'book.add' ,'uses' => 'BookController@addBookCategory']);
            $router->put('bookCategories/{id}', ['as' => 'book.edit' ,'uses' => 'BookController@editBookCategory']);
            $router->get('bookCategories', ['as' => 'bookCategories.show' ,'uses' => 'BookController@showBookCategory']);
            $router->delete('bookCategories/{id}', ['as' => 'bookCategories.delete' ,'uses' => 'BookController@deleteBookCategory']);

            $router->post('book', ['as' => 'book.add' ,'uses' => 'BookController@addBook']);
            $router->put('book/{id}', ['as' => 'book.edit' ,'uses' => 'BookController@editBook']);
            $router->get('book', ['as' => 'book.show' ,'uses' => 'BookController@showBook']);
            $router->delete('book/{id}', ['as' => 'book.delete' ,'uses' => 'BookController@deleteBook']);
            $router->post('showBookWithCategory', ['as' => 'book.delete' ,'uses' => 'BookController@showBookWithCategory']);

            $router->post('bookSeason', ['as' => 'bookSeason.add' ,'uses' => 'BookController@addBookSeason']);
            $router->post('bookSeason/{id}', ['as' => 'bookSeason.edit' ,'uses' => 'BookController@editBookSeason']);
            $router->get('bookAllSeason', ['as' => 'bookSeason.showAll' ,'uses' => 'BookController@showAllBookSeason']);
            $router->get('bookSeason', ['as' => 'bookSeason.show' ,'uses' => 'BookController@showBookSeason']);
            $router->delete('BookSeason/{id', ['as' => 'bookSeason.delete' ,'uses' => 'BookController@deleteBookSeason']);

        });

        $router->group(['prefix' => 'ticket'], function () use ($router)
        {
            $router->post('addTicket', ['as' => 'ticket.add' ,'uses' => 'TicketController@addTicket']);
            $router->post('editTicket/{id}', ['as' => 'ticket.edit' ,'uses' => 'TicketController@editTicket']);
            $router->get('showAllTickets', ['as' => 'ticket.show' ,'uses' => 'TicketController@showAllTickets']);
            $router->get('showUserTicket', ['as' => 'ticket.showUser' ,'uses' => 'TicketController@showUserTicket']);
        });

        $router->group(['prefix' => 'application'], function () use ($router)
        {
            $router->post('uploadApplication', ['as' => 'ticket.add' ,'uses' => 'UploadApplication@uploadApplication']);
        });

        $router->group(['prefix' => 'bookmark'], function () use ($router)
        {
            $router->post('add', ['as' => 'bookmark.add' ,'uses' => 'BookController@addBookmark']);
            $router->post('delete', ['as' => 'bookmark.delete' ,'uses' => 'BookController@deleteBookmark']);
            $router->get('show/{user_id}', ['as' => 'bookmark.show' ,'uses' => 'BookController@showBookmark']);

        });

        $router->group(['prefix' => 'wordCategory'], function () use ($router)
        {
            $router->post('add', ['as' => 'wordCategory.add' ,'uses' => 'WordController@insertWordCategory']);
            $router->put('update/{id}', ['as' => 'wordCategory.update' ,'uses' => 'WordController@updateWordCategory']);
            $router->delete('delete/{id}', ['as' => 'wordCategory.delete' ,'uses' => 'WordController@deleteWordCategory']);
            $router->post('show', ['as' => 'wordCategory.show' ,'uses' => 'WordController@showWordCategory']);
        });

        $router->group(['prefix' => 'wordUser'], function () use ($router)
        {
            $router->post('add', ['as' => 'wordUser.add' ,'uses' => 'WordController@storeUserWord']);
        });

        $router->group(['prefix' => 'word'], function () use ($router)
        {
            $router->post('showCategoryWord', ['as' => 'word.CategoryWord' ,'uses' => 'WordController@showCategoryWord']);
            $router->post('showVoice', ['as' => 'word.CategoryWord' ,'uses' => 'WordController@showVoice']);
        });

        $router->group(['prefix' => 'delete'], function () use ($router)
        {
            $router->post('listDelete', ['as' => 'List.delete' ,'uses' => 'PadcastController@multiDelete']);
        });

    });
});
