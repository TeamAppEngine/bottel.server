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
     * Updates the information of the user
     *
     * @param $userInfo     array, the information of the user that wants to be updated
     * @param $languages    array, the list of languages
     */
    public function updateUserInfo(array $userInfo,$languages){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            $this->clusterPoint->updateUserInfo($userInfo,$languages);
        }
        catch(\Exception $e){
        }
    }

    /**
     * Update the presence of the user
     *
     * @param userID string user id
     */
    public function updateUserPresence($userID){

        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            $this->clusterPoint->updatePresence(
                [
                    "uuid" => $userID,
                    "last_activity" => date("Y-m-d H:i:s")
                ]);
        }
        catch(\Exception $e){
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
            $this->clusterPoint->logConversation($conversationInfo);
            $conversationInfo["partner"] = $user;
            $conversationInfo["user"] = $partner;
            $this->clusterPoint->logConversation($conversationInfo);
            return 1;
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function getOnlineUsersOfCountry($countryID){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            return $this->clusterPoint->getOnlineUsers($countryID);
        }
        catch(\Exception $e){
        }
    }

    public function getIncomingCall($userInfo){
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            $conversationInfo = [
                "owner_id" => $userInfo["uuid"],
                "partner_id" => $userInfo["partner_id"]->__toString(),
                "rate" => 0,
                "duration" => 0,
                "x" => 35.753497,
                "y" => 51.362583,
                "type" => "conversation",
                "country"   => "IR"
            ];

            return $this->clusterPoint->getConversationDetails($conversationInfo);
        }
        catch(\Exception $e){
        }

        return -1;
    }

    /**
     * Gets the role of the user based on Email
     * @param $member   string the member we want to get the role for
     * @return User plus the role of the user,
     *               1 -> Community Hero Member
     *               2 -> Teatak Member
     *               3 -> Community Member
     */
    //TODO: write unit tests
    public function getUserRoleBasedOnEmail($member)
    {
        $this->getUserBasedOnEmail($member);
        $userLevels = $this->UserModel->levels;
        $resultLevel = 3;
        foreach($userLevels as $level)
        {
            if($level->id == 5){//TODO: change 5 to constant
                $resultLevel = 1;
            }
            else if($level->id == 6){//TODO: change 6 to constant
                $resultLevel = 2;
                break;
            }
        }
        $this->UserModel->role = $resultLevel;
        return $this->UserModel;
    }

    public function insertUserInfo($userInfo)
    {
        try {
            if($this->clusterPoint == null)
                $this->clusterPoint = new Libraries\ClusterPoint();
            $this->clusterPoint->insertUser($userInfo);
        }
        catch(\Exception $e){
        }
    }
}