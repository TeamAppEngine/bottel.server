<?php
/**
 * Created by PhpStorm.
 * User: MohammadSadjad
 * Date: 2/24/2015
 * Time: 4:51 PM
 */

namespace App\Http\Libraries;

use League\Flysystem\Exception;

class ClusterPoint
{

    private $cpsConn;
    private $cpsSimple;

    /**
     * Creates a connection to cluster point
     */
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
        $this->cpsConn = new \CPS_Connection(
            new \CPS_LoadBalancer($connectionStrings),
            getenv('DB_NAME'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'),
            'document',
            '//document/id',
            array('account' => 1950)
        );

        // Debug
        //$this->cpsConn->setDebug(true);

        // Creating a CPS_Simple instance
        $this->cpsSimple = new \CPS_Simple($this->cpsConn);
    }

    /**
     * Gets the user based on it's ID
     *
     * @param $id integer the id of the user
     * @return mixed the User that is in the database
     *          -1 the user doesn't exist
     *          -2 connection problem
     */
    public function getUserByID($id)
    {
        $list = array(
            'id' => 'yes'
        );

        $document = null;

        try {
            $document = $this->cpsSimple->lookupSingle($id, $list);
        } catch (Exception $e) {
            return -2;
        }

        if ($document != null)
            return Converter::XML2Array($document);
        else
            return -1;
    }

    /**
     * Gets the user based on it's ID
     *
     * @param $email string the email of the user
     * @return mixed the User that is in the database
     *          -1 the user doesn't exist
     *          -2 connection problem
     */
    public function getUserByEmail($email)
    {
        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $query = CPS_Term($email, 'email');

        $list = array(
            'id' => 'yes'
        );

        try {
            $documents = $cpsSimple->search($query, NULL, NULL, $list);
        } catch (Exception $e) {
            return -2;
        }

        foreach ($documents as $id => $document) {
            return Converter::XML2Array($document);
        }

        return -1;
    }

    /**
     * Insert a user enter the database
     *
     * @param $userInfo
     * @return int the status of the query
     *                              -2 exception
     *                               0 done
     */
    public function insertUser($userInfo)
    {
        try {
            $this->cpsSimple->insertSingle($userInfo["uuid"], $userInfo);
            return 0;
        } catch (\Exception $e) {
            return -2;
        }
    }

    /**
     * Logs that the user has had a conversation
     *
     * @param $conversationInfo Array the information of the conversation
     * @param $incomingCall boolean indicating if it was an incoming or outgoing call
     * @return int the status of the conversation
     */
    public function logConversation($conversationInfo, $incomingCall)
    {
        try {
            $xml = new \SimpleXMLElement('<user/>');
            $conversationArray = [
                "rate" => 0,
                "duration" => 0,
                "partner_id" => $conversationInfo["partner"]["id"],
                "has_hang_up" => 0,
                "is_incoming" => $incomingCall,
                "created_at" => date("Y-m-d H:i:s"),
                "topic" => $conversationInfo["topic"]
            ];
            Converter::Array2XML($conversationArray, $xml);

            if (isset($conversationInfo["user"]["conversations"]["conversation"])) {
                if (gettype($conversationInfo["user"]["conversations"]["conversation"]) == "array") {
                    $conversationInfo["user"]["conversations"]["conversation"][count($conversationInfo["user"]["conversations"]["conversation"])] = $xml;
                } else {
                    $conversation = $conversationInfo["user"]["conversations"]["conversation"];
                    $conversationInfo["user"]["conversations"]["conversation"] = [];
                    $conversationInfo["user"]["conversations"]["conversation"][0] = $conversation;
                    $conversationInfo["user"]["conversations"]["conversation"][1] = $xml;
                }
            } else {
                $conversationInfo["user"]["conversations"] = ["conversation" => [$xml]];
            }

            $this->cpsSimple->updateSingle($conversationInfo["user"]["id"], $conversationInfo["user"]);
        } catch (\Exception $e) {
            return -2;
        }

        return 0;
    }

    /**
     * @param $userInfo    object, The information of the user
     * @param $languages   array, the languages that the user can speak
     * @return int         the result of the cluster point
     *                                          0  -> done successfully
     *                                          -2 -> exception occurred
     */
    public function updateUserInfo($userInfo, $languages)
    {
        try {
            $userInfo["languages"]["language"] = $languages;
            $this->cpsSimple->updateSingle($userInfo["id"], $userInfo);
            return 0;
        }
        catch (\Exception $e) {
            return -2;
        }
    }

    /**
     * @param $countryID
     * @return array
     * @throws \CPS_Exception
     * @throws \Exception
     * @throws null
     */
    public function getOnlineUsers($countryID)
    {
        $query = CPS_Term($countryID, 'country');

        $list = array(
            'id' => 'yes',
            "x" => 'yes',
            "y" => 'yes',
            "about" => "yes",
            "email" => "yes");

        try {
            $documents = $this->cpsSimple->search($query, NULL, NULL, $list);
        }
        catch (\Exception $e) {
            return -2;
        }

        $results = [];
        foreach ($documents as $id => $document) {
            $tempResult = [];
            $tempResult["x"] = $document->x->__toString();
            $tempResult["y"] = $document->y->__toString();
            $tempResult["description"] = $document->about->__toString();
            $tempResult["calls_count"] = 0;
            $tempResult["receive_calls_count"] = 0;
            $tempResult["rate"] = 0;
            $tempResult["minutes_spoken"] = 0;

            if(isset($document->conversations->conversation)) {
                //-----------------------------SUM CONVERSATION OUTGOING----------------
                $count = 0;
                foreach ($document->conversations->conversation as $conversation){
                    if($conversation->is_incoming == 0) {
                        $tempResult["calls_count"]++;
                        $tempResult["rate"] += $conversation->rate;
                    }
                    $tempResult["minutes_spoken"] += $conversation->duration;
                    $count++;
                }
                //-----------------------------SUM CONVERSATION INCOMMING----------------
                $tempResult["receive_calls_count"] =
                    $count-$tempResult["calls_count"];
                //-----------------------------AVG OF RATE CONVERSATION ----------------
                if($count > 0)
                    $tempResult["rate"] = $tempResult["rate"]/$count;
            }

            $tempResult["countries_to"] = ["IR"]; //TODO make it more general
            $tempResult["languages"] = Converter::XML2Array($document->languages)["languages"];

            $results[] = $tempResult;
        }

        return $results;
    }

    /**
     * @param $user
     * @param $partner
     * @return array
     */
    public function getConversationDetails($user,$partner)
    {
        $result = [];
        if(gettype($user["conversations"]["conversation"]) == "array")
            $result["topic"] = $user["conversations"]["conversation"]
            [count ($user["conversations"]["conversation"])-1]->topic->__toString();
        else
            $result["topic"] = $user["conversations"]["conversation"]->topic->__toString();
        $result["partner"] = $partner;
        return $result;
    }
}
