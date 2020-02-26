<?php

include_once('dbc.php');

class Users 
{
    public $userScores;
    
    function __construct() 
    {
        self::validateUserScores();
        $this->userScores = self::getUserScores();
       
    }
    
    function __destruct()
    {
        self::updateUserScores($this->userScores);
    }
    
    /*
     * Sets the current object instance variable userScores
     */
    public function getUserScores() : Array
    {
        $db = new dbc();
        $sql = "select user_name, slack_user_name, score from user_scores;";
        $dbc = $db->connect();
        $stmt = $dbc->prepare($sql);
        $stmt->execute();
        $userScores = Array();
        // perhaps unnecessary object to array conversion here, why not take results as array directly; or return the object;
        while($next = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            
            $userScores[$next['slack_user_name']]= 
                Array(
                        'name' => $next['user_name'],
                        'slackUserName' => $next['slack_user_name'],
                        'score' => $next['score']
                );
            
        }
        return $userScores;
    }
    
    public function validateUserScores()
    {
        $currentUserArray = self::getUserScores();
        $slackUserArray = Slack::getSlackUsers();

        // If any new users add them to the db with default score
        $newUsers = array_diff_key($slackUserArray, $currentUserArray);
        
        foreach ($newUsers as $key)
        {
            $newUser = $key;
            //print_r($newUser);
            self::addUserScore($newUser['name'], $newUser['slackUserName']);
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
        $toDelete = null; // array_intersect($currentUserArray, $deletedUsers);
        if($toDelete)
        {
            foreach ($toDelete as $key)
            {
                deleteUserScore($key);
            }
        }
    }
    
    /*
     * method insertNewUserScore - Creates a new record in the database for a new user_score
     *
     */
    private function addUserScore(String $name, String $slackname)
    {
        $dbc = new dbc;
        $sql = "INSERT INTO USER_SCORES( user_name, slack_user_name, score)
                                VALUES (:uname, :slack_uname, :score);";
        $data = Array
        (
            "uname" => $name,
            "slack_uname" => $slackname,
            "score" => 0
        );
       
        $result = $dbc->query($sql, $data);
        
        if(!$result)
        {
            Logger::writeLogMessage("Error --> insertNewUserScore failed to update the database");
        }
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
                "score" => $key['score'],
                "slack_user_name" => $key['slackUserName']
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
        //print_r($this->userScores[$slackUserName]);
        if(array_key_exists($slackUserName, $this->userScores))
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