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
    return $uuid;
});

Route::bind('email',function($email){
    return $email;
});

Route::bind('country_id',function($country_id){
    return $country_id;
});

Route::bind('partner_id',function($partner_id){
    return $partner_id;
});

//caa126a6-b0b8-440c-8512-9c506264bf61
//Route::pattern('uuid','/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/');


Route::post('api/users', 'UsersController@storePlusVox');

Route::post('api/users/{uuid}', 'UsersController@update');

Route::put('api/users/{uuid}', 'UsersController@update');

Route::post('api/users/{uuid}/presence','UsersController@changePresence');

Route::get('api/countries/{country_id}/users/online','UsersController@getOnlineUsers');

Route::get('api/users/{user_id}/users/{partner_id}/availablity',
    function($user_id,$partner_id){
        return [
            "result" => true
        ];
});

Route::post('api/users/{user_id}/users/{partner_id}/call','UsersController@logCall');

Route::get('api/users/login_session','UsersController@getSession');

Route::get('api/users/{uuid}/image','UsersController@getImage');

Route::get('api/users/{uuid}/match','ConnectionController@getMatchV2'); //TODO

Route::put ('api/users/{uuid}/presence','UsersController@changePresence'); //New

Route::get('api/users/{uuid}/{email}','UsersController@show');

//--------------------- Default -------------------

Route::get('/', function () {
    return view('welcome');
});

