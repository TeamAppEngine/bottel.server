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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

        \App::abort(404, 'function not implemented');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
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
        $userID = $userRepo->getUserBasedOnEmail($email);

        //fill the information of the user
        //if the user didn't exist
        $userInfo = [];

        if ($userID == -1) {
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
                //"languages" => [],
                "country" => "IR",
                //"conversations" => []
            ];
            $userRepo->insertUserInfo($userInfo);
            $voxImplantServer = new \App\Http\Libraries\VoxImplantServer();
            if(!$voxImplantServer->createUser(str_replace('@','_',$email),$email))
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
     * Display the specified resource.
     *
     * @param  User $user
     * @return Response
     */
    //TODO: write unit tests
    public function show(User $user, User $partner)
    {

        if ($user->toArray() == [] || $partner->toArray() == [])
            \App::abort(404, 'The API doesn\'t exist');
        $imageUrl = "";
        $imageIndex = Libraries\ImageHelper::getTheCurrentImageIndex($partner);
        if ($imageIndex != -1) //the user has an image
        {
            $imageUrl = \Request::url() . "/image";
        }

        $sessionRepo = new SessionRepository(new \App\Session());
        $session = $sessionRepo->getSessionDetails($user, $partner);
        $topicRepo = new TopicRepository(new \App\Topic());
        $topic = $topicRepo->getTopicBasedOnId($session->topic_id);
        $userRepo = new UserRepository(new \App\User());
        $tempTopic = [];
        $tempTopic['id'] = $topic->id;
        $tempTopic['text'] = $topic->text;
        $tempTopic['extra_question'] = $topic->extraQuestions()->FirstOrFail()->text;
        $prompts = $topic->prompts;
        $promptArray = [];
        foreach($prompts as $prompt)
        {
            $tempPrompt['id'] = $prompt->id;
            $tempPrompt['text'] = $prompt->text;
            $promptArray[] = $tempPrompt;
        }
        $tempTopic['prompts'] = $promptArray;

        $result = [
            "partner" => [
                "email" => $partner->email,
                "first_name" => $partner->first_name,
                "last_name" => $partner->last_name,
                "birth_date" => $partner->date_of_birth,
                "country_iso" => $partner->country->iso_code,
                "profile_image" => $imageUrl,
                "gender" => $partner->gender
            ],
            "role" => $userRepo->getUserRoleBasedOnEmail($partner)->role,
            "topic" => $tempTopic
        ];

        return json_encode($result);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  $userID string the information of the user
     * @return Response
     */
    public function changePresence(Request $request, $userID)
    {
        $userRepo = new UserRepository();
        $userIDCluster = $userRepo->getUserBasedOnUuid($userID);

        if ($userIDCluster == -1)
            \App::abort(404, 'The API doesn\'t exist');

        $userRepo->updateUserPresence($userID);

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
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  userID String the information of the user
     * @return Response
     */
    //TODO: must write tests
    public function update(Request $request, $userID)
    {
        $userRepo = new UserRepository();
        $userIDCluster = $userRepo->getUserBasedOnUuid($userID);
        if ($userIDCluster == -1)
            \App::abort(404, 'The API doesn\'t exist');

        if ($request->get('full_name') &&
            $request->get('about') &&
            $request->get('languages')
        ) {
            $userInfo = [
                "uuid" => $userID,
                "full_name" => $request->get('full_name'),
                "about" => $request->get('about')
            ];

            $languages = json_decode($request->get('languages'));

            $userRepo->updateUserInfo($userInfo,$languages);
        } else {
            \App::abort(400, 'The contract of the api was not met');
        }

        return json_encode([]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Get the session for a user
     *
     * @param Request $request the request sent to the user
     * @return Response         the user information
     */
    public function getImage(User $user)
    {

        if ($user->toArray() == [])
            \App::abort(404, 'The API doesn\'t exist');
        $imageUrl = "";
        $imageIndex = Libraries\ImageHelper::getTheCurrentImageIndex($user);
        if ($imageIndex != -1) //the user has an image
        {
            $filePath = storage_path() . "\app\avatars\\" . $user->uuid . "$imageIndex.jpg";

            return \Response::download($filePath, $user->first_name . ".jpg", [
                'Content-Type' => 'text/jpeg',
            ]);
        }
        \App::abort(404, 'The user doesn\'t have a valid image');
    }

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


}
