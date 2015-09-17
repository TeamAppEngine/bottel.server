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


Route::post('api/users', 'UsersController@storePlusVox'); //Changed

Route::post('api/users/{uuid}', 'UsersController@update');

Route::put('api/users/{uuid}', 'UsersController@update');

Route::get('api/users/login_session','UsersController@getSession');

Route::get('api/users/{uuid}/image','UsersController@getImage');

Route::get('api/users/{uuid}/match','ConnectionController@getMatchV2'); //TODO

Route::put ('api/users/{uuid}/presence','UsersController@changePresence'); //New

Route::post('api/users/{uuid}/presence','UsersController@changePresence'); //New

Route::get('api/users/{uuid}/{email}','UsersController@show');

//--------------------- Default -------------------

Route::get('/', function () {
    return view('welcome');
});

