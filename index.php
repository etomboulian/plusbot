<?php
/*
PlusBot Slack Add-on Application
Created by etomboulian for ActiveNetTeam Slack group
12/22/2019
*/
// BEGIN APPLICATION

// Import functions for plusbot
require_once('classes/Slack.php');
require_once('classes/Users.php');

// Create objects required for the program function
$Slack = new Slack();
// Get input from Slack post
$sInput = file_get_contents('php://input');
$arInput = json_decode($sInput, true);

// Assume not authorized slack post before validation
$bIsAuthorizedSlackMessage = false;

// If challenge message is received, then respond appropriately and quit
if($arInput['challenge'] ?? false)
{
    // Validate and reply to challenge message
    $Slack->challengeMessageValidateAndReply($arInput['challenge']); 
    die();
}

// If there is a token value validate the token
if($arInput['token'] ?? false)
{
    // Get token from post and check if it is authorized
    $sToken = $arInput['token'];
    $bIsAuthorizedSlackMessage = $Slack->isValidToken($sToken);
    // If not valid/authorized slack post, then disregard message posted and quit
    if(!$bIsAuthorizedSlackMessage)
    {
        Logger::writeLogMessage("Error --> Failed  validation. \n");
        die();
    }
}

// If an authorized public channel message is received ...
if ($arInput['event']['type'] === 'message' && $bIsAuthorizedSlackMessage)
{
    // Get some parameters from the message body
    $sMessage = $arInput['event']['text'];
	$postUser = $arInput['event']['user'];
    $postChannel = $arInput['event']['channel'];
    // check for users found
    $usersFound = $Slack->checkMessageForUsers($sMessage);
    // check for things found
    $arThingsFound = $Slack->checkMessageForThings($sMessage);
    // foreach user found, update the user score and post back a message
    foreach ($usersFound as $key)
    {
       
        $users = new Users();
        $targetUser = $key[0];
        $karma = $key[1];
        if($users->processUserScore($targetUser, $karma))
        {
            $Slack->postResponseMessage($users, $karma, $postChannel, $targetUser, $postUser);
        }
    }
    // foreach thing found, update the thing score and post back a message
//     foreach($arThingsFound as $key => $value)
//     {
//         $thingName = $value[0];
//         $karma = $value[1];
//         $db->processThingScore($thingName, $karma);

//     }
    // If no users or things were found, check the message for other commands
    if(empty($usersFound) && empty($arThingsFound))
    {
        $Slack->checkOtherFunctions($sMessage);
    }

}

?>