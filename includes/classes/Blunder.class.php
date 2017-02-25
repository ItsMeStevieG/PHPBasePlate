<?php
/** Blunder Error Logger v1.0
Copyright (c) 2017 Steven Graham

USAGE: errorLogger([ string $email ] [, string $logpath]);

Example 1: $elog=new errorLogger(); //Default Mode: Log to Logs Folder
Example 2: $elog=new errorLogger("user@domain.com"); //Email Errors and Log to Logs Folder
Example 3: $elog=new errorLogger(NULL,"/var/log/phperrors.php"); //No Email but specify custom path
**/



class Blunder
{
	public $logPath=NULL;
	//public $timeZone="UTC";
	private $errorMessage=NULL;
	private $email=NULL;
	
	public function __construct($email=NULL,$logpath="auto")
	{	
		if($logpath=="auto")
		{
			$this->logPath=LOC_LOGPATH;
		}
		else
		{
			$this->logPath=$logpath;
		}
		
		if(!file_exists($this->logPath))
		{
			if(!$fh = fopen($this->logPath, 'w'))
			{
				$this->dieError("Cant create error log file. (Path: ".$this->logPath.")");
			}
			fclose($fh);
			chmod($this->logPath, 0777); 
		}
		
		if(isset($email) && $email!=NULL)
		{
			$this->email=$email;
		}
		
		set_error_handler(array($this, 'handleError'),E_ALL & ~E_NOTICE);
	}
	
	public function handleError($type, $message, $file, $line, $vars)
	{
		switch($type) 
		{ 
			case 1: // 1 // 
				$type_str = 'E_ERROR';
				$readable_type = 'ERROR' ;
				break;
			case 2: // 2 // 
				$type_str = 'E_WARNING';
				$readable_type = 'WARNING';
				break;
			case 4: // 4 // 
				$type_str = 'E_PARSE';
				$readable_type = 'PARSE';
				break;
			case 8: // 8 // 
				$type_str = 'E_NOTICE';
				$readable_type = 'NOTICE';
				break;
			case 16: // 16 // 
				$type_str = 'E_CORE_ERROR';
				$readable_type = 'CORE_ERROR';
				break;
			case 32: // 32 // 
				$type_str = 'E_CORE_WARNING';
				$readable_type = 'CORE_WARNING';
				break;
			case 64: // 64 // 
				$type_str = 'E_COMPILE_ERROR';
				$readable_type = 'COMPILE_ERROR';
				break;
			case 128: // 128 // 
				$type_str = 'E_COMPILE_WARNING';
				$readable_type = 'COMPILE_WARNING';
				break;
			case 256: // 256 // 
				$type_str = 'E_USER_ERROR';
				$readable_type = 'USER_ERROR';
				break;
			case 512: // 512 // 
				$type_str = 'E_USER_WARNING';
				$readable_type = 'USER_WARNING';
				break;
			case 1024: // 1024 // 
				$type_str = 'E_USER_NOTICE'; 
				$readable_type = 'USER_NOTICE';
				break;
			case 2048: // 2048 // 
				$type_str = 'E_STRICT';
				$readable_type = 'STRICT';
				break;
			case 4096: // 4096 // 
				$type_str = 'E_RECOVERABLE_ERROR';
				$readable_type = 'RECOVERABLE_ERROR';
				break;
			case 8192: // 8192 // 
				$type_str = 'E_DEPRECATED';
				$readable_type = 'DEPRECATED';
				break;
			case 16384: // 16384 // 
				$type_str = 'E_USER_DEPRECATED';
				$readable_type = 'USER_DEPRECATED';
				break;
		}
		
		$this->errorMessage =  '[ '.date('r').' ] '.$readable_type.': '.$message.' in '.$file.' on line '.$line."\r\n";
	   // for development simply ECHO $errormessage;
		
		if($this->email!="")
		{
			$this->sendEmail();
		}
		
		file_put_contents($this->logPath, $this->errorMessage, FILE_APPEND);
		
		if(ini_get('display_errors')==1)
		{
			die($this->errorMessage);
		}
	}
	
	public function triggerError($error)
	{
		trigger_error($error,E_USER_ERROR);
	}
	
	public function sendEmail()
	{
		//die("HOST: ".$_SERVER['SERVER_NAME']);		
		$message=$this->errorMessage;
		$to = $this->email;
		$subject = "errorLogger Notification";
		$headers = "From: noreply@".$_SERVER['SERVER_NAME'] . "\r\n" .
			"X-Mailer: PHP/" . phpversion()."\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1";
		if(!@mail($to, $subject, $message, $headers))
		{
			$this->triggerError("Unable to Send Email");
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public function showErrors($bool)
	{
		ini_set('display_errors', (int) $bool);
	}
	
	public function setErrorLevel($errlevel)
	{
		$this->errorLevel=$errlevel;
		error_reporting($this->errorLevel);
	}
    
    public function dieError($ErrorMessage,$errorNumber=NULL)
    {
        if(isset($errorNumber) && $errorNumber!=NULL) //Has Error Number And Message
        {
            die("<strong style='color: red;'>ERROR[$errorNumber]: </strong>$ErrorMessage");
        }
        else
        {
            die("<strong style='color: red;'>ERROR: </strong>$ErrorMessage");
        }
    }
}

//die("ERROR REPORTING ".error_reporting());

//$elog=new errorLogger();
//$elog=new errorLogger("root@localhost");
//$val=0/0;
//$elog->triggerError("Custom Error");
//echo($elog->logPath);
?>