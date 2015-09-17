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
            $this->UserModel = $this->UserModel->where('uuid', $uuid)->first();
            if($this->UserModel == null)
                $this->UserModel = new User();
            return $this->UserModel;
        }
        catch(\Exception $e){
            $this->UserModel = new User();
            return $this->UserModel;
        }
    }

    /**
     * @param $id           integer, the id of the user
     * @return              User, the information of the user based on id
     */
    public function getUserBasedOnId($id){
        try{
            $this->UserModel = $this->UserModel->where('id',$id)->first();
            if($this->UserModel == null)
                $this->UserModel = new User();
            return $this->UserModel;
        }
        catch(\Exception $e){
            $this->UserModel = new User();
            return $this->UserModel;
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
     */
    public function updateUserInfo(array $userInfo){

        foreach($userInfo as $key => $value)
        {
            $this->UserModel->{$key} = $value;
        }

        $this->UserModel->save();
        return $this->UserModel;
    }

    /**
     * Update the presence of the user
     *
     * @param $userInfo     array, the information of the user that wants to be updated
     */
    public function updateUserPresence($presenceId){

        $presence = $this->UserModel->lastPresence();
        $hasChanged = false;
        if(isset($presence->pivot))
        {
            $fromTime = strtotime($presence->pivot->updated_at);
            $toTime = strtotime(date("Y-m-d H:i:s"));
            if(round(abs($toTime - $fromTime) / 60,2) < \Config::get('constants.presence'))
            {
                if($presence->id == $presenceId)
                    $this->UserModel->presence()->updateExistingPivot($presence->id, [
                        'updated_at' => date("Y-m-d H:i:s",$fromTime)
                    ], true);
                else
                    $hasChanged = true;
            }
            else {
                $hasChanged = true;
            }
        }
        else
            $hasChanged = true;

        if($hasChanged == true)
            $this->UserModel->presence()->attach($presenceId);
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

    /**
     * Gets the role of the user
     * @return User plus the role of the user,
     *               1 -> Community Hero Member
     *               2 -> Teatak Member
     *               3 -> Community Member
     */
    //TODO: write unit tests
    public function getUserRole()
    {
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

    public function setUserLevel($int)
    {
        //TODO: set the user level
        //TODO: write unit tests and stuff
        /*$levelRepo = new LevelRepository(new \App\Level());
        $this->GroupModel->users()->attach($user);
        $this->UserModel->attach*/
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