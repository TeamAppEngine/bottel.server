<?php namespace Repositories;

use App\Http\Libraries;

class UserRepository {

    private $clusterPoint = null;

    /**
     * @param $uuid         string the uuid of the user
     * @return              User, the information of the user based on uuid
     */
    public function getUserBasedOnUuid($uuid){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            return $this->clusterPoint->getUserByID($uuid);
        }
        catch(\Exception $e){
            return -1;
        }
    }

    /**
     * @param $email        string, the email of the user
     * @return              User, the information of the user based on email
     */
    public function getUserBasedOnEmail($email){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            return $this->clusterPoint->getUserByEmail($email);
        }
        catch(\Exception $e){
            return -1;
        }
    }

    /**
     * Insert the newly created user in the database
     *
     * @param $userInfo
     * @return int if the user is inserted return 0
     *                              else   return -1
     */
    public function insertUserInfo($userInfo)
    {
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            if($this->clusterPoint->insertUser($userInfo) == 0)
                return 0;
            else
                return -1;
        }
        catch(\Exception $e){
            return -1;
        }
    }

    /**
     * Logs that the both users have been in a conversation
     *
     * @param $conversationInfo Array the information of the conversation
     * @return int
     */
    public function logCall($conversationInfo){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();

            $partner = $conversationInfo["partner"];
            $user = $conversationInfo["user"];
            if($this->clusterPoint->logConversation($conversationInfo,0) == -2)
                return -1;
            $conversationInfo["partner"] = $user;
            $conversationInfo["user"] = $partner;
            if($this->clusterPoint->logConversation($conversationInfo,1) == -2)
                return -1;
            return 0;
        }
        catch(\Exception $e){
            return -1;
        }
    }

    /**
     * Updates the information of the user
     *
     * @param $userInfo     object, the information of the user that wants to be updated
     * @param $languages    array, the list of languages
     * @return int the status of the update
     *                                          -2 ClusterPoint exception
     *                                          0 done successfully
     */
    public function updateUserInfo($userInfo,$languages){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            if($this->clusterPoint->updateUserInfo($userInfo,$languages) == -2)
                return -1;
            return 0;
        }
        catch(\Exception $e){
            return -1;
        }
    }

    /**
     * Update the presence of the user
     *
     * @param $user
     * @param $presence
     * @internal param string $userID user id
     * @return int
     */
    public function updateUserPresence($user,$presence){

        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            $user["last_activity"] = date("Y-m-d H:i:s");
            $user["is_present"] = $presence == "available" ? 1:0;
            if($this->clusterPoint->updateUserInfo($user, $user["languages"]["language"]) == -2)
                return -2;
            return 0;
        }
        catch(\Exception $e){
            return -2;
        }
    }

    /**
     * Get the information of the online users in a country
     *
     * @param $countryID int the id of the country
     * @return array|int
     */
    public function getOnlineUsersOfCountry($countryID){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            $onlineUsers = $this->clusterPoint->getOnlineUsers($countryID);
            if($onlineUsers == -2)
                return -2;
            return $onlineUsers;
        }
        catch(\Exception $e){
            return -2;
        }
    }

    /**
     * Get the partner of the partner that was calling
     *
     * @param $userInfo array the information of the user and the partner
     * @return array|int
     */
    public function getIncomingCall($userInfo){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            $partnerInfo = $this->clusterPoint->getConversationDetails($userInfo["user"],$userInfo["partner"]);
            if($partnerInfo == -2)
                return -2;
            return $partnerInfo;
        }
        catch(\Exception $e){
            return -2;
        }
    }
}