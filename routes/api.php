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
        Route::post('trip/delete', 'TripController@delete');
        Route::post('trip/update', 'TripController@update');

        Route::get('destination', 'DestinationController@get');
        Route::post('destination', 'DestinationController@create');
        Route::post('destination/update', 'DestinationController@update');
        Route::post('destination/delete', 'DestinationController@delete');

        Route::get('transport', 'TransportController@get');
        Route::post('transport', 'TransportController@create');
        Route::post('transport/update', 'TransportController@update');
        Route::post('transport/delete', 'TransportController@delete');
        Route::post('batch/transport', 'TransportController@create_batch');

        Route::get('accommodation', 'AccommodationController@get');
        Route::post('accommodation', 'AccommodationController@create');
        Route::post('accommodation/update', 'AccommodationController@update');
        Route::post('accommodation/delete', 'AccommodationController@delete');
        Route::post('batch/accommodation', 'AccommodationController@create_batch');

        Route::get('itinerary', 'ItineraryController@get');
        Route::post('itinerary', 'ItineraryController@create');
        Route::post('itinerary/update', 'ItineraryController@update');
        Route::post('itinerary/delete', 'ItineraryController@delete');
        Route::post('batch/itinerary', 'ItineraryController@create_batch');

    });

    Route::get('accommodation/search', 'AccommodationController@search');

    Route::post('register', 'Auth\RegisterController@register');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('user', 'UserController@find');
});
