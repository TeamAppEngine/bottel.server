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

    public function updateUserInfo($userInfo, $languages)
    {
        // Creating a CPS_Simple instance
        $cpsSimple = new \CPS_Simple($this->cpsConn);

        $cpsSimple->updateSingle($userInfo["uuid"], $userInfo);

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
