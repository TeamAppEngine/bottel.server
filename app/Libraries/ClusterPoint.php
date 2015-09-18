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

    private $cpsConn;

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
    }

    public function getUserByEmail($email)
    {
        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $query = CPS_Term($email, 'email');

        $list = array(
            'id' => 'yes'
        );

        $documents = $cpsSimple->search($query, NULL, NULL, $list);

        foreach ($documents as $id => $document) {
            return $document->id;
        }

        return -1;
    }

    public function insertUser($userInfo)
    {

        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $cpsSimple->insertSingle($userInfo["uuid"], $userInfo);
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

        $query = CPS_Term($countryID, 'country');

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

            //------------------------------------------------------------------
            //Get User Conversations
            $query = CPS_Term("conversation", 'type') . CPS_Term($id, "user_id");

            $list = array(
                'partner_id' => 'yes',
                "duration" => 'yes',
                "rate" => 'yes',
                "country" => "yes",
                "is_incoming" => "yes");



            // Searching for documents
            // note that only the query parameter is mandatory - the rest are optional
            $searchRequest = new \CPS_SearchRequest($query, NULL, NULL, $list);

            // Get the list of distinct values of "Country" field, ordered by field "Country" descending
            $aggregate = "count(partner_id) ";
            $searchRequest->setAggregate($aggregate);
            $searchResponse = $this->cpsConn->sendRequest($searchRequest);
            if ($searchResponse->getHits() > 0) {
                foreach ($searchResponse->getDocuments() as $idAggregate => $documentAggregate) {
                    echo 'Density: ' . $documentAggregate->Density . '<br />';
                }
                foreach ($searchResponse->getAggregate() as $qu => $aggr) {
                    echo '<br />Aggregation query: ' . $qu . '<br />';
                    foreach ($aggr as $key => $val) {
                        echo 'Country: ' . $val->Country . '<br />';
                    }
                }
            } else {
                echo 'Nothing found.';
            }
            //--------------------------------------------------------------------

            $tempResult["calls_count"] = 0;
            $tempResult["receive_calls_count"] = 0;
            $tempResult["countries_to"] = [];
            $tempResult["rate"] = 0;
            $tempResult["minutes_spoken"] = 0;
            $tempResult["languages"] = [];
            $tempResult["id"] = $document->email->__toString();
            $results[] = $tempResult;
        }

        dd($results);
        return $results;
    }

    public function postConversation($conversationInfo){

        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $cpsSimple->insertSingle($conversationInfo["id"], $conversationInfo);
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

    public function getUserByID($id)
    {

        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $query = CPS_Term($id, 'id');

        $list = array(
            'id' => 'yes'
        );

        $documents = $cpsSimple->search($query, NULL, NULL, $list);

        foreach ($documents as $id => $document) {
            return $document->id;
        }

        return -1;
    }
}
