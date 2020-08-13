<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'middleware' => 'api',
], function ($router) {
    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::post('logout', 'Auth\LoginController@logout');
        Route::post('user', 'UserController@update');

        Route::get('trip', 'TripController@find');
        Route::post('trip', 'TripController@create');
        Route::post('delete_trip', 'TripController@delete');
        Route::post('update_trip', 'TripController@update');


    });

    Route::get('hotel', 'HotelController@find');
    
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('user', 'UserController@find');
});
