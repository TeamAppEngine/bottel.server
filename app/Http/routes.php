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

//TODO
Route::post('api/users', 'UsersController@storePlusVox');

//TODO
Route::post('api/users/{uuid}', 'UsersController@update');

//TODO
Route::put('api/users/{uuid}', 'UsersController@update');

//TODO
Route::post('api/users/{uuid}/presence','UsersController@changePresence');

//TODO
Route::get('api/countries/{country_id}/users/online','UsersController@getOnlineUsers');

//TODO
Route::get('api/users/{user_id}/users/{partner_id}/availablity',
    function($user_id,$partner_id){
        return [
            "result" => true
        ];
});

//TODO
Route::post('api/users/{user_id}/users/{partner_id}/call','UsersController@logCall');

//TODO
Route::get('api/users/{user_id}/users/{partner_id}/incoming_info','UsersController@show');

//TODO
Route::get('api/users/login_session','UsersController@getSession');

//TODO
Route::get('api/users/{uuid}/image','UsersController@getImage');

//TODO
Route::get('api/users/{uuid}/match','ConnectionController@getMatchV2'); //TODO

//TODO
Route::put ('api/users/{uuid}/presence','UsersController@changePresence'); //New

//TODO
Route::get ('api/generate','UsersController@generatePin'); //New



//--------------------- Default -------------------

Route::get('/', function () {
    return view('welcome');
});

