<?php

require_once('Secrets.php');
require_once('Logger.php');

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
        $ch = curl_init("https://slack.com/api/users.list?token=".self::O_AUTH_TOKEN);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $userArrayJson = curl_exec($ch);
        $userArray = json_decode($userArrayJson, TRUE);
        $userArray = $userArray['members'];
        $userArraySummary = Array();
        //print_r($userArray);
        //die();
        foreach ($userArray as $key => $value)
        {
            if(isset($value['real_name']))
            {
                
                $id = $value['id'];
                $real_name = $value['real_name'];
                $isBot = $value['is_bot'];
                $deleted = $value['deleted'];

                $userArraySummary[$id] =
                    Array(  'name'     => $real_name,
                            'slackUserName' => $id,
                            'isBot'     => $isBot,
                            'deleted'   => $deleted
                        );
            }
        }
        return $userArraySummary;
    }
    
    public function checkMessageForUsers(String& $message) : Array
    {
        $matches = Array();
        $usersFound = Array();
        preg_match_all(self::USER_REGEX, $message, $matches);

        foreach($matches[0] as $key)
        {
            $close_match = substr($key, 2);
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
        foreach ($matches[0] as $key)
        {
            $match = substr(substr($key,0, strpos($value, " ")),1);
            $match = strtolower($match);
            $karma = substr($matches[0],-2,2);
            $thingsFound[] = Array($match, $karma);
        }
        return $thingsFound;
    }
    
    public function postResponseMessage(&$users, String &$karma, String &$channel, String &$targetUser, String &$postUser) 
    {
        $message = self::getResponseMessage($users, $karma, $targetUser, $postUser);
        $slack_webhook_url = "";
        if ($channel == self::CHANNELS['anetquestions']) 
        {
            $slack_webhook_url= 'https://hooks.slack.com/services/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';
        } 
        else if( $channel == self::CHANNELS['t2']) 
        {
            $slack_webhook_url = 'https://hooks.slack.com/services/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';
        }
        elseif($channel == self::CHANNELS['china-team']) 
        {
            $slack_webhook_url = 'https://hooks.slack.com/services/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';
        } 
        elseif($channel == self::CHANNELS['general'])
        {
            $slack_webhook_url = "https://hooks.slack.com/services/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";
        }
        else 
        {
            $slack_webhook_url = 'https://hooks.slack.com/services/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';
        }
        self::doPost($slack_webhook_url, $message);
    }
    
    public function getResponseMessage($users, $karma, $targetUser, $postUser) : String
    {
        if ($targetUser == $postUser)
        {          
            $message = "Uh uh don't think so. Try again with another user ". $users->userScores[$postUser]['name']."\n";
        }
        else 
        {
            if ($karma == "++")
            {
                $message = "Good Job ".$users->userScores[$targetUser]['name']." you got another point. Current Score: ".$users->userScores[$targetUser]['score']."\n";
            }
            elseif ($karma == "--")
            {
                $message = "Tisk Tisk, ".$users->userScores[$targetUser]['name']." do better next time";
            }
        }
        echo $message;
        return $message;
    }
    
    private function doPost($url, $message) 
    {
        $headers = Array( 'Content-type: application/json');
        $data =  json_encode(Array ('text' => $message));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
        $response = curl_exec($ch);
        //print_r($response);
    }

}

?>