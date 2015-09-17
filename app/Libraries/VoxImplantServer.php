<?php
/**
 * Created by PhpStorm.
 * User: MohammadSadjad
 * Date: 2/24/2015
 * Time: 4:51 PM
 */

namespace App\Http\Libraries;

class VoxImplantServer
{
    const API_KEY = "6ee7d4ed-7bac-4bba-bc7f-42155ace8d6e";
    const ID = 218189;

    public function __construct()
    {
    }

    public function createUser($userName, $displayName)
    {
        $restApi = new RestAPI();
        $sendData = array(
            "account_id" => self::ID,
            "api_key" => self::API_KEY,
            "user_name" => $userName,
            "user_display_name" => $displayName,
            "user_password" => 1234567
        );

        $apiResult = $restApi->CallAPIGuzzle("GET", "https://api.voximplant.com/platform_api/AddUser/", $sendData);
        $result = json_decode($apiResult);

        if ($result->{"result"} == 1)
            return $this->assignToApplication($userName);
        else
            return false;
    }

    public function assignToApplication($userName)
    {
        $restApi = new RestAPI();

        $sendData = array(
            "account_id" => self::ID,
            "api_key" => self::API_KEY,
            "user_name" => $userName,
            "application_name" => "app"
        );

        $apiResult = $restApi->CallAPIGuzzle("GET", "https://api.voximplant.com/platform_api/BindUser/", $sendData);
        $result = json_decode($apiResult);

        if ($result->{"result"} == 1)
            return true;
        else
            return false;
    }
}
