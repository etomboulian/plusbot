<?php

include_once 'Secrets.php';

class Slack
{
    const SLACK_TOKEN = Secrets::SLACK_TOKEN;
    const O_AUTH_TOKEN = Secrets::O_AUTH_TOKEN;
    const USER_REGEX = '/<@[<>a-zA-z0-9]+>(\s|\S|\s+)(\+\+|--)/';
    const THINGS_REGEX = '/@[a-zA-z0-9]+(\s|\S|\s+)(\+\+|--)/';
    
    const CHANNELS  = 
        Array (
            'general'       => 'C2525T76Z',
            'anetquestions' => 'CBED4NVFS',
            't2'            => 'C78D9QWV8',
            'china-team'    => 'CFMRL1WQ5'
    );

    public function isValidToken(String &$token) : bool
    {
        return $token === self::SLACK_TOKEN;
    }

    public function challengeMessageValidateAndReply(String& $input) : void
    {
        echo $input;
    }
    
    public static function getSlackUsers() : Array
    {
        $ch = curl_init("https://slack.com/api/users.list?token=".O_AUTH_TOKEN);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $userArrayJson = curl_exec($ch);
        $userArray = json_decode($userArrayJson, TRUE);
        $userArray = $userArray['members'];
        $userArraySummary = Array();
        foreach ($userArray as $value)
        {
            $userArraySummary[$value['id']] =
                Array(  'name'     => $value['real_name'],
                        'slackUserName' => $value['id'],
                        'isBot'     => $value['is_bot'],
                        'deleted'   => $value['deleted']
                     );
        }
        return $userArraySummary;
    }
    
    public function checkMessageForUsers(String& $message) : Array
    {
        $matches = Array();
        $usersFound = Array();
        preg_match_all(self::USER_REGEX, $message, $matches);
        foreach( $matches as $value) 
        {
            //Check if @user or not
            // if @user then find user data and update score then post response
            $close_match = substr($value, 2);
            $karma = substr($close_match,-2,2);
            $slackUserName = substr($close_match,0,-4);
            $usersFound[] = Array($slackUserName, $karma);
        }
        return $usersFound;        
    }
    
    public function checkMessageForThings(String& $message) : Array
    {
        $matches = Array();
        $thingsFound = Array();
        preg_match_all(self::THINGS_REGEX, $message, $matches);
        foreach ($matches as $value)
        {
            $match = substr(substr($value,0, strpos($value, " ")),1);
            $match = strtolower($match);
            $karma = substr($matches[0],-2,2);
            $thingsFound[] = Array($match, $karma);
        }
        return $thingsFound;
    }
    
    public function postResponseMessage(&$users, String &$karma, String &$channel, String &$targetUser, String &$postUser) 
    {

        $message = &getResponseMessage($users, $karma, $targetUser, $postUser);
        if ($channel == self::CHANNELS['anetquestions']) 
        {
            $slack_webhook_url= 'https://hooks.slack.com/services/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';
            doPost($slack_webhook_url, $message);
        } 
        else if( $channel == self::CHANNELS['t2']) 
        {
            $slack_webhook_url = 'https://hooks.slack.com/services/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';
            doPost($slack_webhook_url, $message);
        }
        elseif($channel == self::CHANNELS['china-team']) 
        {
            $slack_webhook_url = 'https://hooks.slack.com/services/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';
            doPost($slack_webhook_url,$message);
            
        } 
        else 
        {
            $slack_webhook_url = 'https://hooks.slack.com/services/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';
            doPost($slack_webhook_url, $message);
        }
        
        
    }
    
    public function &getResponseMessage(&$users, &$karma, &$targetUser, &$postUser) : String
    {
        if ($targetUser == $postUser)
        {
            $message = "Uh uh don't think so. Try again with another user ". $users->userScores[$postUser]['realName'];
        }
        else 
        {
            if ($karma == "++")
            {
                $message = "Good Job ".$users->userScores[$targetUser]['realName']." you got another point. Current Score: ";
            }
            elseif ($karma == "--")
            {
                $message = "Tisk Tisk, ".$users->userScores[$targetUser]['realName']." do better next time";
            }
        }
        return $message;
    }
    
    function doPost($url, $message) 
    {
        $headers = Array( 'Content-type: application/json');
        $data = json_encode( Array ('text' => $message) );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);
    }

}

?>