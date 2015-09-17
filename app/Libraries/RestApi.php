<?php
/**
 * Created by PhpStorm.
 * User: MohammadSadjad
 * Date: 11/7/14
 * Time: 11:13 AM
 */

namespace App\Http\Libraries;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class RestAPI
 *
 * This class is responsible to handle the REST API calls
 */
class RestAPI {

    /**
     * NAME:
     * CallAPI
     *
     * DESCRIPTION:
     * This function calls a rest API and returns the results
     *
     * URL:
     * This function can't be called directly from the browser
     *
     * PROCESS:
     * This function is an external library
     *
     * INPUT:
     * @param $method       string the type of method call (POST, GET, etc)
     * @param $url          string the url of the api
     * @param bool $data    string the data that is passed to the API as an associative array
     *
     * OUTPUT:
     * @return mixed        the response of the API as an array
     */
    public function CallAPICurl($method, $url, $data = false)
    {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    public function CallAPIGuzzle($method, $url, $data = false)
    {
        $guzzleClient = new Client();

        $res = null;

        try {
            if ($method == "GET")
                $res = $guzzleClient->get($url, ["query" => $data]);
            else if ($method == "POST")
                $res = $guzzleClient->post($url, ["query" => $data]);

            return $res->getBody();
        }
        catch(RequestException $e){
            echo "except".$e->getMessage();
        }
    }
} 