<?php

class Logger
{
    const LOGFILE = "../logs/plusBotLog.txt";
    
    public static function openFile(string &$fileName, bool $append = true)
    {
        $fileMode = ($append) ? 'a+' : 'w+';
        try 
        {
            $fh = fopen($fileName, $fileMode);
        } 
        catch (Exception $e) 
        {
            echo $e.getmessage();
        }
        return $fh;
    }

    public static function closeFile($fileHandle) : bool
    {
        $success = fclose($fileHandle);
        if(!$success)
        {
            echo "Unable to close file handle: ".$fileHandle;
            return false;
        }
        return true;
    }

    public static function writeLogMessage(string &$message)
    {
        $fh_logFile = openFile(self::LOGFILE);
        fwrite($fh_logFile, $message."\n");
        closeFile($fh_logFile);
    }    
    
 }

?>
