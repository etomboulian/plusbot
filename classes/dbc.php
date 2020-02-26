<?php

include_once("Secrets.php");
include_once("Logger.php");

class dbc 
{
    // Create database connection to plusbot mysql database
    private $dbserver = "localhost";
    private $dbname = "plusbot";
    private $username = Secrets::DB_USERNAME;
    private $password = Secrets::DB_PASSWORD;
    private $dbc;

    function __construct() 
    {
        // build the $dsn string from params
        $dsn = "mysql:host=".$this->dbserver.";dbname=".$this->dbname;
        try     // make the db connection and assign to instance variable
        {
            $this->dbc = new PDO($dsn, $this->username, $this->password);
            
        } 
        catch (PDOException $e)  // catch exception if thrown
        {
            Logger::writeLogMessage("Error --> DBConnection Failed: ".$e->getMessage());
        }
    }
    
    function __destruct()
    {
        // Manually close the db connection on object destruction
        $this->dbc = null;
    }

    function connect()
    {
        return $this->dbc;
    }
    
    function query(String &$sql, Array &$data = null)
    {
        $stmt = $this->dbc->prepare($sql);
        if($data !== null) $stmt->execute($data);
        else $stmt->execute();
        return $stmt;
    }
    
//     /*
//      *  method checkUserScoreExists - checks to see if the found user exists in the database
//      *  @parm - String $slack_user_name
//      *  @return - boolean to indicate success/fail
     
//     public function checkUserScoreExists(String $slack_user_name) : bool
//     {
//         $sql = "SELECT user_score_id FROM USER_SCORES WHERE slack_user_name = :username";
//         $stmt = $this->dbc->prepare($sql);
//         $data = Array ("username" => $slack_user_name);
//         $stmt->execute($data);
//         $result = $stmt->rowCount();
//         return $result !== 0;
//     }
    
//     /*
//      *  method getUserScore - gets the user array record from the database
//      *  @param - String $slack_user_name 
//      *  @return - userArray - Array of user_score data
//      */
//     public function getUserScore(String $slack_user_name) : Array
//     {
//         $sql = "SELECT * FROM USER_SCORES WHERE slack_user_name = :username";
//         $stmt = $this->dbc->prepare($sql);
//         $data = Array ("username" => $slack_user_name);
//         $stmt->execute($data);
//         $result = $stmt->fetch(PDO::FETCH_ASSOC);
//         return $result;
//     }
    
//     /*
//      *  method updateUserScore - Takes a userscore array and updates the corresponding db record
//      *  @param - user_score Array
//      *  @return - boolean indication of success or failure
//      */
//     public function updateUserScore(Array& $users) : bool
//     {
//         if(!empty($users['user_name']) && !empty($users['slack_user_name']) && !empty($users['score']))
//         {
//             $sql = "UPDATE user_scores SET score = :score WHERE slack_user_name = :slack_user_name";
//             $data = Array
//             (
//                 "score" => $users['score'],
//                 "slack_user_name" => $users['slack_user_name']
//             );
//             $stmt = $this->dbc->prepare($sql);
//             $result = $stmt->execute($data);
//             return $result;
//         }
//         else 
//         {
//             Logger::writeLogMessage("Error --> updateUserScore passed malformed users array");  
//             return false;
//         }
        
//     }
    
//     /*  
//      * method insertNewUserScore - Creates a new record in the database for a new user_score
//      * 
//      */
//     public function insertNewUserScore(String $name, String $slackname) : bool
//     {
//         $sql = "INSERT INTO USER_SCORES( user_name, slack_user_name, score)
//                                 VALUES (:uname, :slack_uname, :score);";
//         $data = Array 
//         (
//             "uname" => $name,
//             "slack_name" => $slackname,
//             "score" => 0
//         );
//         $stmt = $this->dbc->prepare($sql);
//         $result = $stmt->execute($data);
//         if(!$result)
//         {
//             Logger::writeLogMessage("Error --> insertNewUserScore failed to update the database");
//         }
//         return $result;
//     }
//     /*
//      *  method processUserScore - Proceses the received user_score and update the current score or inserts with 0 value
//      */
//      public function processUserScore(String $slackname, String $karma) : bool 
//      {
//      // check if the value already exists in the database
//          if($this->checkUserScoreExists($slackname)) 
//          {
//              $userArray = $this->getUserScore($slackname);
//              $scoreChange = 0;
//              if ($karma === "++") 
//                 ++$scoreChange;
//              elseif ($karma === "--")
//                 --$scoreChange;
//              else 
//                 die();
//              $userArray['score'] += $scoreChange;
//              $result = $this->updateUserScore($userArray);
//          } 
//          else 
//          {
//             $result = $this->insertNewUserScore($name, $slackname);
//          }
//          return $result;
//     } */
    
//     public function processThingScore(String $thingName, String $karma)
//     {
        
//         if ($karma === "++")
//             ++$scoreChange;
//         elseif ($karma === "--")
//             --$scoreChange;
//         else
//             die();
        
//     }

}

?>