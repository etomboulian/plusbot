<?php


class Users 
{
    public $userScores;
    
    function __construct() 
    {
        validateUserScores();
        $this->userScores = getUserScores();
    }
    
    function __destruct()
    {
        updateUserScores($this->userScores);
    }
    
    /*
     * Sets the current object instance variable userScores
     */
    private function getUserScores() : Array
    {
        $db = new dbc();
        $sql = "select username, slackUserName, score from userScores";
        $result = $db->query($sql);
        $userScores = Array();
        while($next = $result->fetchObject())
        {
            $userScores[$next->slackUserName] = 
                Array(
                        'name' => $next->name,
                        'slackUserName' => $next->slackUserName,
                        'score' => $next->score
                );
        }
        return $userScores;
    }
    
    private function validateUserScores()
    {
        $currentUserArray = getUserScores();
        $slackUserArray = Slack::getSlackUsers();
        
        // If any new users add them to the db with default score
        $newUsers = array_diff_key($currentUserArray, $slackUserArray);
        foreach ($newUsers as $key)
        {
            $newUser = $slackUserArray[$key];
            addUserScore($newUser['name'], $newUser['slackUserName']);
        }
        
        // If any deleted users exist remove them from the db
        
        // First create an array of the deleted users from the existing data
        $deletedUsers = Array();
        foreach ($slackUserArray as $key => $value)
        {
            if ($value['deleted'] === true)
            {
                $deletedUsers[$key] = $value;
            }
        }
        // Then figure out which users that alaready exists are now deleted and delete them
        $toDelete = array_intersect($currentUserArray, $deletedUsers);
        foreach ($toDelete as $key)
        {
            deleteUserScore($key);
        }
           
    }
    
    /*
     * method insertNewUserScore - Creates a new record in the database for a new user_score
     *
     */
    private function addUserScore(String $name, String $slackname) : bool
    {
        $sql = "INSERT INTO USER_SCORES( user_name, slack_user_name, score)
                                VALUES (:uname, :slack_uname, :score);";
        $data = Array
        (
            "uname" => $name,
            "slack_name" => $slackname,
            "score" => 0
        );
        $stmt = $this->dbc->prepare($sql);
        $result = $stmt->execute($data);
        if(!$result)
        {
            Logger::writeLogMessage("Error --> insertNewUserScore failed to update the database");
        }
        return $result;
    }
    
    private function deleteUserScore(String & $slackUserName)
    {
        $dbc = new dbc();
        $sql = "delete from user_scores where slackUserName = :slackUserName";
        $data = Array( 'slackUserName' => $slackUserName);
        $dbc->query($sql, $data);
    }
    
    
    /*
     *  method updateUserScore - Takes a userscore array and updates the corresponding db record
     *  @param - user_score Array
     *  @return - boolean indication of success or failure
     */
    private function updateUserScores(Array& $userScores) 
    {
        $db = new dbc();
        foreach($userScores as $key)
        {
            
            $sql = "UPDATE user_scores SET score = :score WHERE slack_user_name = :slack_user_name";
            $data = Array
            (
                "score" => $userScores[$key]['score'],
                "slack_user_name" => $userScores[$key]['slack_user_name']
            );
            $db->query($sql, $data);
        }
    }
        
    /*
     *  method processUserScore - Proceses the received user_score and update the current score
     */
    public function processUserScore(String $slackUserName, String $karma) : bool
    {
        // check if the value already exists in the database
        if($this->userScores['slackUserName'] === $slackUserName)
        {
            $scoreChange = 0;
            if      ($karma === "++") { $scoreChange = 1; }
            elseif  ($karma === "--") { $scoreChange = -1; }
                  
            $this->userScores[$slackUserName]['score'] += $scoreChange;
        }
        else
        {
           Logger::writeLogMessage("Error Users -> procesUserScore  :  User requested to update doesn't exist in memory");
           return false;
        }
      return true;
    }
}

?>