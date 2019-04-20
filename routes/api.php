<?php

use Illuminate\Http\Request;

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


Route::group(['prefix' => 'v1'], function () {

    Route::post('login', 'Api\V1\UserController@login');
    Route::post('register', 'Api\V1\UserController@register');

    Route::group(['middleware' => 'auth:api'], function(){
        Route::get('details', 'Api\V1\UserController@details');
        Route::get('users', 'Api\V1\UserController@index');
        Route::get('user/{id}', 'Api\V1\UserController@show');
    });
});