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
    const API_KEY = "5ffb90d7-d604-4b2b-9e66-21d1c10a307b";
    const ID = 237404;

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
            "application_name" => "staging-bottel"
        );

        $apiResult = $restApi->CallAPIGuzzle("GET", "https://api.voximplant.com/platform_api/BindUser/", $sendData);
        $result = json_decode($apiResult);
        if ($result->{"result"} == 1)
            return true;
        else
            return false;
    }
}
