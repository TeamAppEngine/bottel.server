<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::bind('uuid',function($uuid){

    $userModel = new \Repositories\UserRepository(new \App\User);
    return $userModel->getUserBasedOnUuid($uuid);

});

Route::bind('email',function($email){

    $userModel = new \Repositories\UserRepository(new \App\User);
    return $userModel->getUserBasedOnEmail($email);

});

//caa126a6-b0b8-440c-8512-9c506264bf61
//Route::pattern('uuid','/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/');

//--------------------   V1  ---------------------

Route::post('api/v1/users', 'UsersController@store');

Route::post('api/v1/users/{uuid}', 'UsersController@update');

Route::put('api/v1/users/{uuid}', 'UsersController@update');

Route::get('api/v1/users/login_session','UsersController@getSession');

Route::get('api/v1/users/{uuid}/image','UsersController@getImage');

Route::get('api/v1/users/{uuid}/token','ConnectionController@getToken');

Route::get('api/v1/users/{uuid}/match','ConnectionController@getMatch');

Route::get('api/v1/users/{uuid}/{email}','UsersController@show');

//--------------------   V2  ---------------------

Route::post('api/v2/users', 'UsersController@storePlusVox'); //Changed

Route::post('api/v2/users/{uuid}', 'UsersController@update');

Route::put('api/v2/users/{uuid}', 'UsersController@update');

Route::get('api/v2/users/login_session','UsersController@getSession');

Route::get('api/v2/users/{uuid}/image','UsersController@getImage');

Route::get('api/v2/users/{uuid}/match','ConnectionController@getMatchV2'); //TODO

Route::put ('api/v2/users/{uuid}/presence','UsersController@changePresence'); //New

Route::post('api/v2/users/{uuid}/presence','UsersController@changePresence'); //New

Route::get('api/v2/users/{uuid}/{email}','UsersController@show');

//--------------------- Default -------------------

Route::get('/', function () {
    return view('welcome');
});

