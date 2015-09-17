<?php
/**
 * Created by PhpStorm.
 * User: MohammadSadjad
 * Date: 2/24/2015
 * Time: 4:51 PM
 */

namespace App\Http\Libraries;

class ClusterPoint
{
    const API_KEY = "6ee7d4ed-7bac-4bba-bc7f-42155ace8d6e";
    const ID = 218189;

    public function __construct()
    {
        // Connection hubs
        $connectionStrings = array(
            'tcp://cloud-eu-0.clusterpoint.com:9007',
            'tcp://cloud-eu-1.clusterpoint.com:9007',
            'tcp://cloud-eu-2.clusterpoint.com:9007',
            'tcp://cloud-eu-3.clusterpoint.com:9007'
        );

        // Creating a CPS_Connection instance
        $cpsConn = new \CPS_Connection(
            new \CPS_LoadBalancer($connectionStrings),
            getenv('DB_NAME'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'),
            'document',
            '//document/id',
            array('account' => 1950)
        );

        // Debug
        //$cpsConn->setDebug(true);

        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($cpsConn);

        $query = CPS_Term('Iran');

        $offset = 0;

        $docs = 5;

        $list = array(
            'id' => 'yes',
            'x' => 'yes',
            'y' => 'yes',
            'about' => 'yes',
            'country' => 'yes',
            'conversations' => 'yes',
        );

        $documents = $cpsSimple->search($query, $offset, $docs, $list);
        /*$doc = $cpsSimple->retrieveSingle(1);
        var_dump($doc);*/
        // Looping through results

        foreach ($documents as $id => $document) {
            echo $document->x . ' ' . $document->y."\n";
        }
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
