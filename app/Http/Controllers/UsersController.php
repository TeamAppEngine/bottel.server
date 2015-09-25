<?php

namespace App\Http\Controllers;

use Illuminate\Cache\Repository;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Libraries;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
//use Rhumsaa\Uuid;
use App\User;
use Repositories\SessionRepository;
use Repositories\TopicRepository;
use Repositories\UserRepository;
use Repositories\CountryRepository;

class UsersController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return \Response
     */
    //TODO: must write tests
    public function storePlusVox(Request $request)
    {
        //get the email and password from the input
        $email = "";
        $password = "";
        if ($request->get('email') && $request->get('password')) {

            $password = $request->get('password');
            if (Libraries\InputValidator::isEmailValid($request->get('email'))) {
                $email = $request->get('email');
            } else {
                \App::abort(400, 'The contract of the api was not met');
            }
        } else
            \App::abort(400, 'The contract of the api was not met');


        //get the user based on the email
        $userRepo = new UserRepository();
        $user = $userRepo->getUserBasedOnEmail($email);

        //fill the information of the user
        //if the user didn't exist
        $userInfo = [];

        if ($user == -1) {
            $userInfo = [
                "uuid" => \Rhumsaa\Uuid\Uuid::uuid4()->toString(),
                "full_name" => "",
                "x" => 35.908592,
                "y" => 50.881902,
                "email" => $email,
                "password" => sha1($password),
                "is_present" => "",
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                "last_activity" => date("Y-m-d H:i:s"),
                "languages" => [],
                "country" => "IR",
                "conversations" => []
            ];
            $userRepo->insertUserInfo($userInfo);
            $voxImplantServer = new \App\Http\Libraries\VoxImplantServer();
            if (!$voxImplantServer->createUser(str_replace('@', '_', $email), $email))
                \App::abort(500, 'The voximplant server didn\'t reply');
        } else {
            \App::abort(409, 'The email is already in use');
        }

        //send the results back to the user
        return json_encode([
            "user_id" => $userInfo["uuid"]
        ]);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  $user    object the information of the user
     * @param  $partner object the information of the partner
     * @return \Response
     */
    public function logCall(Request $request, $user, $partner)
    {
        if ($user == -1)
            \App::abort(404, 'The API doesn\'t exist');
        else if($user == -2)
            \App::abort(500, 'cluster point didn\'t reply');

        if ($partner == -1)
            \App::abort(404, 'The API doesn\'t exist');
        else if($partner == -2)
            \App::abort(500, 'cluster point didn\'t reply');

        $userRepo = new UserRepository();

        $result = [];
        if ($request->get('topic')) {
            $conversationInfo = [
                "user" => $user,
                "partner" => $partner,
                "topic" => $request->get('topic')];
            $result = $userRepo->logCall($conversationInfo);
        } else
            \App::abort(400, 'The contract of the api was not met');

        return json_encode($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  $user object The information of the user
     * @return \Response
     */
    //TODO: must write tests
    public function update(Request $request, $user)
    {
        $userRepo = new UserRepository();

        if ($user == -1)
            \App::abort(404, 'The API doesn\'t exist');
        else if($user == -2)
            \App::abort(500, 'cluster point didn\'t reply');

        $result = [];
        if ($request->get('full_name') &&
            $request->get('about') &&
            $request->get('languages')
        ) {
            $user["full_name"] = $request->get('full_name');
            $user["about"] = $request->get('about');

            $languages = json_decode($request->get('languages'));

            $result = $userRepo->updateUserInfo($user, $languages);
        } else {
            \App::abort(400, 'The contract of the api was not met');
        }

        return json_encode($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param $user
     * @return Response
     */
    public function changePresence(Request $request, $user)
    {
        $userRepo = new UserRepository();

        if ($user == -1)
            \App::abort(404, 'The API doesn\'t exist');

        $userRepo->updateUserPresence($user,"available");

        return json_encode([]);
    }

    /**
     * Update the specified resource in storage.
     * @param
     * @return Response
     */
    public function getOnlineUsers($countryID)
    {
        $userRepo = new UserRepository();
        $onlineUsers = $userRepo->getOnlineUsersOfCountry($countryID);
        return json_encode($onlineUsers);
    }

    /**
     * Display the specified resource.
     *
     * @param $user
     * @param $partner
     * @internal param $userID
     * @internal param $partnerID
     * @return \Response
     */
    //TODO: write unit tests
    public function show($user, $partner)
    {
        $userRepo = new UserRepository();

        if ($user == -1)
            \App::abort(404, 'The API doesn\'t exist');

        if ($partner == -1)
            \App::abort(404, 'The API doesn\'t exist');

        $userInfo = [
            "user" => $user,
            "partner" => $partner
        ];

        $getInfo = $userRepo->getIncomingCall($userInfo);

        return json_encode($getInfo);
    }

    //TODO---------------------Refactor from here below-------------------------

    /**
     * Get the image of the user
     *
     * @param  Request $request the username and password of the user
     * @return Response             the image download
     */
    public function getSession(Request $request)
    {

        //get the email and password from the input
        $email = "";
        $password = "";
        if ($request->get('email') && $request->get('password')) {

            $password = $request->get('password');
            if (Libraries\InputValidator::isEmailValid($request->get('email'))) {
                $email = $request->get('email');
            } else {
                \App::abort(400, 'The contract of the api was not met');
            }
        } else
            \App::abort(400, 'The contract of the api was not met');


        //get the user based on the email
        $userRepo = new UserRepository(new User());
        $user = $userRepo->getUserBasedOnEmail($email);

        //fill the information of the user
        //if the user didn't exist
        $userInfo = [];

        if (!isset($user->password)) {
            \App::abort(404, 'The user doesn\'t exist in the database');
        } else {
            if ($user->password != sha1($password)) {
                \App::abort(404, 'The user doesn\'t exist in the database');
            }

            $imageUrl = \Request::getRequestUri() . $user->uuid . "/image";

            $userInfo = [
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "birth_date" => $user->date_of_birth,
                "gender" => $user->gender,
                "country_iso" => $user->country->iso_code,
                "profile_image" => $imageUrl,
                "user_id" => $user->uuid,
                "role" => $userRepo->getUserRole()->role,
                "email" => $user->email,
            ];
        }

        //send the results back to the user
        return json_encode($userInfo);
    }

    public function generatePin() {
        $path = storage_path() . "/data/countries.json"; // ie: /var/www/laravel/app/storage/json/filename.json

        if (!\File::exists($path)) {
            throw new \Exception("Invalid File");
        }

        $file = \File::get($path); // string
        $fileArray = json_decode($file);
        foreach($fileArray as $key => $countries){
            $temp = [];
            $temp["uuid"] = \Rhumsaa\Uuid\Uuid::uuid4()->toString();
            $temp["full_name"] = "Arsalan";
            $temp["email"] = "a.yarveisi@gmail.com";
            $temp["password"] = 12345678;
            $temp["is_present"] = true;
            $temp["about"] ="I have a bachelors and I love Sports";
            $temp["created_at"] = date("Y-m-d H:i:s");
            $temp["updated_at"] = date("Y-m-d H:i:s");
            $temp["last_activity"] = date("Y-m-d H:i:s");
            $temp["country"] = $countries->{"iso-code"};
            $temp["type"] = "user";
            $temp["languages"] = [
                "language" => "English"
            ];
            $temp["conversations"] = "";
            $temp["x"] = $countries->x;
            $temp["y"] = $countries->y;
            $fileArray[$key] = $temp;
        }

        $xml = new \SimpleXMLElement('<users/>');

        Libraries\Converter::Array2XML($fileArray, $xml);
        //dd($xml);
        return \Response::make($xml->asXML(), '200')->header('Content-Type', 'text/xml');
        // Verify Validate JSON?

        // Your other Stuff

    }
}
