<?php
require_once('Logger.php');

class xxScoreboard
{
    public $logger;
    public $usersArray;
    public $thingsArray;

    public function __construct(Logger& $logger)
    {
        $this->logger = $logger;
        $this->setUsersArray();
        $this->setThingsArray();
    }

    public function getUsersArray()
    {
        return $this->usersArray;
    }

    public function setUsersArray()
    {
        $usersArray = $this->getAndValidateUsersArray();
        if ($usersArray === false)
        {
            echo "ERROR --> Unable to set Users Array\n";
        }
    }

   public function getThingsArray()
   {
       return $this->thingsArray;
   }

   public function setThingsArray()
   {
       $this->logger->getThings();
       $ar_things = $this->logger->getThings();
       if($ar_things)
       {
           $this->$ar_things = $ar_things;
       }
       else 
       {
           echo "ERROR --> unable to set things array.\n";
       }
   }

   public function getAndValidateUsersArray()
   {
       $result = false;
       $loggedScores = $this->logger->getUsersArray();
       echo $loggedScores;
       return $result;

   }


}

?>
