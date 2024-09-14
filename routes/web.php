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
            $router->post('padcast-category', ['as' => 'padcast.add', 'uses' => "PadcastController@addPadcastCategory"]);
            $router->get('padcast-category/{id}', ['as' => 'padcast.show', 'uses' => "PadcastController@showPadcastCategory"]);
            $router->put('padcast-category/{id}', ['as' => 'padcast.update', 'uses' => "PadcastController@updatePadcastCategory"]);
        });

        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->put('showUsers', ['as' => 'users.show', 'uses' => "UsersController@showAllUsers"]);
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
            $router->get('bookCategories/{id}', ['as' => 'bookCategories.show' ,'uses' => 'BookController@showBookCategory']);
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
    });
});
