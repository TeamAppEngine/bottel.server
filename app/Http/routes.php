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
use Repositories\UserRepository;

Route::bind('uuid',function($uuid){
    $userRepo = new UserRepository();
    return $userRepo->getUserBasedOnUuid($uuid);
});

Route::bind('email',function($email){
    $userRepo = new UserRepository();
    return $userRepo->getUserBasedOnEmail($email);
});

Route::bind('country_id',function($country_id){
    return $country_id;
});

Route::bind('partner_id',function($partner_id){
    return $partner_id;
});

//caa126a6-b0b8-440c-8512-9c506264bf61
//Route::pattern('uuid','/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/');

//TODO Needs Check
Route::post('api/users', 'UsersController@storePlusVox');

Route::post('api/users/{uuid}', 'UsersController@update');

Route::put('api/users/{uuid}', 'UsersController@update');

Route::post('api/users/{uuid}/presence','UsersController@changePresence');

Route::post('api/users/{uuid}/users/{email}/call','UsersController@logCall');

Route::get('api/users/{user_id}/users/{partner_id}/availability',
    function($user_id,$partner_id){
        return [
            "result" => true
        ];
});

Route::get('api/countries/{country_id}/users/online','UsersController@getOnlineUsers');

Route::get('api/users/{uuid}/users/{email}/incoming_info','UsersController@show');

//TODO
Route::get('api/users/login_session','UsersController@getSession');

Route::get ('api/generate','UsersController@generatePin'); //New

//--------------------- Default -------------------

Route::get('/', function () {
    return view('welcome');
});

