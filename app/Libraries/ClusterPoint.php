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
        }
        catch(Exception $e)
        {
            return -2;
        }

        if($document != null)
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

        try{
            $documents = $cpsSimple->search($query, NULL, NULL, $list);
        }
        catch(Exception $e)
        {
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
        }
        catch(\Exception $e){
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
    public function logConversation($conversationInfo, $incomingCall){
        try{
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
            Converter::Array2XML($conversationArray,$xml);

            if(isset($conversationInfo["user"]["conversations"]["conversation"])) {
                if(gettype($conversationInfo["user"]["conversations"]["conversation"]) == "array") {
                    $conversationInfo["user"]["conversations"]["conversation"][
                    count($conversationInfo["user"]["conversations"]["conversation"])] = $xml;
                }
                else{
                    $conversation = $conversationInfo["user"]["conversations"]["conversation"];
                    $conversationInfo["user"]["conversations"]["conversation"] = [];
                    $conversationInfo["user"]["conversations"]["conversation"][0] = $conversation;
                    $conversationInfo["user"]["conversations"]["conversation"][1] = $xml;
                }
            }
            else {
                $conversationInfo["user"]["conversations"] = ["conversation" => [$xml]];
            }

            $this->cpsSimple->updateSingle($conversationInfo["user"]["id"], $conversationInfo["user"]);
        }
        catch(\Exception $e)
        {
            return -2;
        }

        return 0;
    }

    //TODO
    public function getConversationDetails($conversationInfo){
        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $query = CPS_Term($conversationInfo["owner_id"], 'owner_id')
            .CPS_Term($conversationInfo["partner_id"], 'partner_id')
            .CPS_Term('conversation', 'type');

        $list = array(
            'id' => 'yes',
            'topic' => 'yes'
        );

        $documents = $cpsSimple->search($query, NULL, NULL, $list);

        $result = [];

        foreach ($documents as $id => $document) {
            $result["topic"] = $document->topic->__toString(); //TODO solve multiple issue
        }

        $query = CPS_Term($conversationInfo["partner_id"], 'user_id').CPS_Term('language','type');

        $list = array("language" => "yes");

        $documentsLanguage = $cpsSimple->search($query, NULL, NULL, $list);

        $result["languages"] = [];
        foreach ($documentsLanguage as $idLanguage => $documentLanguage) {
            $result["languages"][] = $documentLanguage->language->__toString();
        }

        $query = CPS_Term($conversationInfo["partner_id"], 'id')
            .CPS_Term('user','type');

        $list = array(
            'full_name' => 'yes',
            'country' => 'yes'
        );

        $documents = $cpsSimple->search($query, NULL, NULL, $list);

        foreach ($documents as $id => $document) {
            $result["full_name"] = $document->full_name->__toString();
            $result["country"] = $document->country->__toString();
        }

        return $result;
    }

    //TODO
    public function getPartnerInfo($userID){

        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $query = CPS_Term($userID, 'id')
            .CPS_Term('user','type');

        $list = array(
            'full_name' => 'yes',
            'country' => 'yes'
        );
        dd($userID);
        $documents = $cpsSimple->lookupSingle($userID, $list);

        dd($documents);
        $result = [];

        foreach ($documents as $id => $document) {
            $result["full_name"] = $document->full_name->__toString();
            $result["country"] = $document->country->__toString();
        }

        return $result;
    }

    public function updatePresence($userInfo)
    {
        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $cpsSimple->partialReplaceSingle($userInfo["uuid"], $userInfo);
    }

    public function getOnlineUsers($countryID){
        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $query = CPS_Term($countryID, 'country').CPS_Term('user','type');

        $list = array(
            'id' => 'yes',
            "x" => 'yes',
            "y" => 'yes',
            "about" => "yes",
            "email" => "yes");

        $documents = $cpsSimple->search($query, NULL, NULL, $list);
        $results = [];
        foreach ($documents as $id => $document) {
            $tempResult = [];
            $tempResult["x"] = $document->x->__toString();
            $tempResult["y"] = $document->y->__toString();
            $tempResult["description"] = $document->about->__toString();

            //-----------------------------SUM CONVERSATION OUTGOING----------------
            //Get User Conversations
            $query = CPS_Term("conversation", 'type') . CPS_Term($id, "owner_id");

            $list = array(
                'partner_id' => 'yes',
                "duration" => 'yes',
                "rate" => 'yes',
                "country" => "yes"
            );

            // Searching for documents
            // note that only the query parameter is mandatory - the rest are optional
            $searchRequest = new \CPS_SearchRequest($query, NULL, NULL, $list);
            $searchResponse = $this->cpsConn->sendRequest($searchRequest);
            $tempResult["calls_count"] = $searchResponse->getHits();

            //--------------------------------------------------------------------
            //-----------------------------SUM CONVERSATION INCOMMING----------------
            //Get User Conversations
            $query = CPS_Term("conversation", 'type') . CPS_Term($id, "partner_id");

            $list = array(
                'partner_id' => 'yes',
                "duration" => 'yes',
                "rate" => 'yes',
                "country" => "yes"
            );

            // Searching for documents
            // note that only the query parameter is mandatory - the rest are optional
            $searchRequest = new \CPS_SearchRequest($query, NULL, NULL, $list);
            $searchResponse = $this->cpsConn->sendRequest($searchRequest);
            $tempResult["receive_calls_count"] = $searchResponse->getHits();
            //--------------------------------------------------------------------

            $tempResult["countries_to"] = ["IR"]; //TODO make it more general

            //-----------------------------AVG OF RATE CONVERSATION ----------------
            //Get User Conversations
            $query = CPS_Term("conversation", 'type') . CPS_Term($id, "partner_id");

            $list = array(
                'partner_id' => 'yes',
                "duration" => 'yes',
                "rate" => 'yes',
                "country" => "yes"
            );

            // Searching for documents
            $searchRequest = new \CPS_SearchRequest($query, NULL, NULL, $list);

// Get the list of distinct values of "Country" field, ordered by field "Country" descending
            $aggregate = 'AVG(rate)';
            $searchRequest->setAggregate($aggregate);

            $searchResponse = $this->cpsConn->sendRequest($searchRequest);

            $tempResult["rate"] = $searchResponse->getAggregate()["AVG(rate)"]->AVG_rate->__toString();
            //--------------------------------------------------------------------
            //-----------------------------AVG OF RATE CONVERSATION ----------------
            //Get User Conversations
            $query = CPS_Term("conversation", 'type') . CPS_Term($id, "partner_id");

            $list = array(
                'partner_id' => 'yes',
                "duration" => 'yes',
                "rate" => 'yes',
                "country" => "yes"
            );

            // Searching for documents
            $searchRequest = new \CPS_SearchRequest($query, NULL, NULL, $list);

// Get the list of distinct values of "Country" field, ordered by field "Country" descending
            $aggregate = 'SUM(duration)';
            $searchRequest->setAggregate($aggregate);

            $searchResponse = $this->cpsConn->sendRequest($searchRequest);

            $tempResult["minutes_spoken"] = $searchResponse->getAggregate()["SUM(duration)"]->SUM_duration->__toString();
            //--------------------------------------------------------------------

            $query = CPS_Term($id, 'user_id').CPS_Term('language','type');

            $list = array(
                "language" => "yes");

            $documentsLanguage = $cpsSimple->search($query, NULL, NULL, $list);

            $tempResult["languages"] = [];
            foreach ($documentsLanguage as $idLanguage => $documentLanguage) {
                    $tempResult["languages"][] = $documentLanguage->language->__toString();
            }

            $tempResult["id"] = $document->email->__toString();
            $results[] = $tempResult;
        }

        return $results;
    }

    public function updateUserInfo($userInfo, $languages)
    {
        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $cpsSimple->partialReplaceSingle($userInfo["uuid"], $userInfo);

        //delete all the languages
        $query = CPS_Term("language", 'type') . CPS_Term($userInfo["uuid"], "user_id");

        $list = array(
            'id' => 'yes'
        );

        $documents = $cpsSimple->search($query, NULL, NULL, $list);

        $ids = [];
        foreach ($documents as $id => $document) {
            $ids[] = $id;
        }

        if(count($ids) > 0) {
            $cpsSimple->delete($ids);
        }
        //end of delete languages

        //insert new languages
        $i = 1;
        $insertMultipleLanguages = [];
        foreach ($languages as $language) {
            $insertMultipleLanguages[$userInfo["uuid"] . "l" . ($i++)] = [
                "type" => "language",
                "user_id" => $userInfo["uuid"],
                "language" => $language
            ];
        }
        $cpsSimple->insertMultiple($insertMultipleLanguages);
    }
}
