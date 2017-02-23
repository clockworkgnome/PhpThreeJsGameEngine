<?php
require_once('db.inc.php');
require_once('../lib/password.php');
$newUserName= $_REQUEST['username'];
$newPass =$_REQUEST['password'];
$newConf =$_REQUEST['confirm'];
$newEmail =$_REQUEST['email'];
$newEmail2=$_REQUEST['email2'];
$flags="";
$flagString="";
$formEnteries=0;
if(isset ($newUserName)){// check to see if username was enetred
	$formEnteries=$formEnteries+1;
	//check to see if user name is in use
	$sNameSafe = $GLOBALS['MySQL']->process_db_input($newUserName, A_TAGS_STRIP);
	$sSQL = "SELECT `id` FROM `s_members` WHERE `name`='{$sNameSafe}'";
	$iID = (int)$GLOBALS['MySQL']->getOne($sSQL);
	if($iID > 0){
		// user name is taken display 
		$flagString=$flagString."User name taken.<br>";
		$flags=$flags."2 ";
	}else{
		// user name is good
		if(isset($newPass)){
			//user entered a password
			$formEnteries=$formEnteries+1;
			if(isset($newConf)){
				//user confirmed password
				$formEnteries=$formEnteries+1;
				if($newPass==$newConf){
					//passwords match
					if(PasswordRequirements(4,$newPass)==true){
						//password is stronk
						if(isset($newEmail)){
							// user entered an email
							$formEnteries=$formEnteries+1;
							if(isset($newEmail2)){
								// user confirmed email
								$formEnteries=$formEnteries+1;
								if($newEmail==$newEmail2){
									//emails matched
									if(validEmail($newEmail)==true){
										// email is valid
										
										// all is good with the world
										$flagString=$flagString."Welcome to compuplague <br>";
										$flags=$flags."0";
									}else{
										// email not valid
										$flagString=$flagString."email not valid <br>";
										$flags=$flags."10 ";
									}
									
								}else{
									//emails did not match
									$flagString=$flagString."email's did not match<br>";
									$flags=$flags."9 ";
								}
							}else{
								//user did not confirm email
								$flagString=$flagString."email not confirmed.<br>";
								$flags=$flags."8 ";
							}
						}else{
							// user did not enter email
							$flagString=$flagString."No email entered.<br>";
							$flags=$flags."7 ";
						}
						
					}else{
						//password too weak
						$flagString=$flagString."password too weak.<br>";
						$flags=$flags."6 ";
					}
				}else{
					//passwords did not match
					$flagString=$flagString."passwords did not match.<br>";
					$flags=$flags."5 ";
				}
			}else{
				//user did not confirm password
				$flagString=$flagString."Did not confirm a password.<br>";
				$flags=$flags."4 ";
			}
		}else{
			//new password not entered 
			$flagString=$flagString."Did not enter a password.<br>";
			$flags=$flags."3 ";
		}
	}
}else{
	// did not enter user name
	$flagString=$flagString."Did not enter a username.<br>";
	$flags=$flags."1 ";
	
}

$flags = explode(" ", $flags);
//check flags

if($formEnteries==0){
	//first time user is here
	// display form 
	displayForm("","","","","");
}elseif($formEnteries<5){
	// form not complete 
	echo $flagString;// display errors 
	// redisplay form
	displayForm($newUserName,$newPass,$newConf,$newEmail,$newEmail2);
}elseif($formEnteries==5){
	// form complete check for flags
	if(count($flags)<=1 && $flags[0]==0){
		// no flags add user
		//echo "user added.";
		// hash the password
		$newPass=password_hash($newPass, PASSWORD_DEFAULT);
		$sSQL = 'INSERT INTO `jujugameengine`.`s_members` (`id`, `name`, `pass`, `status`, `email`) VALUES (NULL, \''.$newUserName.'\', \''.$newPass.'\', \'offline\', \''.$newEmail.'\');';
		$GLOBALS['MySQL']->res($sSQL);
		header("Location: ../index.php");// go to lobby after user was added 
	}else{
		// there was flags
		echo $flagString;// display errors
		// redisplay form
		displayForm($newUserName,$newPass,$newConf,$newEmail,$newEmail2);
	}
}

/**
 Validate an email address.
 Provide email address (raw input)
 Returns true if the email address has the email
 address format and the domain exists.
 */
function validEmail($email)
{
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex)
	{
		$isValid = false;
	}
	else
	{
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64)
		{
			// local part length exceeded
			$isValid = false;
		}
		else if ($domainLen < 1 || $domainLen > 255)
		{
			// domain part length exceeded
			$isValid = false;
		}
		else if ($local[0] == '.' || $local[$localLen-1] == '.')
		{
			// local part starts or ends with '.'
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			$isValid = false;
		}
		else if
		(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
				str_replace("\\\\","",$local)))
		{
			// character not valid in local part unless
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/',
					str_replace("\\\\","",$local)))
			{
				$isValid = false;
			}
		}
		if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) 
		{
			// domain not found in DNS
			$isValid = false;
		}
	}
	return $isValid;
}

function PasswordRequirements($ComplexityCount,$newPass) {
	$Count = 0;
	if(preg_match("/\d/", $newPass) > 0) {// one number
		$Count++;
	}
	if(preg_match("/[A-Z]/", $newPass) > 0) {// one upper case
		$Count++;
	}
	if(preg_match("/[a-z]/", $newPass) > 0) {// one lower case
		$Count++;
	}
	if(preg_match("/[^\da-zA-Z]/", $newPass) > 0) {// one special char
		$Count++;
	}
	//echo "count: ".$Count."<br>";
	if($Count >= $ComplexityCount) {
		return true;
	} else {
		
		return false;
	}
}

function displayForm($username,$password,$confirm,$email,$email2){
	$strForm="
			<!DOCTYPE html>
<html>
<head>
<meta charset=\"ISO-8859-1\">
<title>New Account sign up</title>
</head>
<body>
<form class=\"new_account_form\" method=\"post\" action=\"noob.php\">
    <div>Username: <input type=\"text\" name=\"username\" value=\"$username\"/></div>
    <div>Password: <input type=\"password\" name=\"password\" value=\"$password\"/></div>
    <div>Confirm Password: <input type=\"password\" name=\"confirm\" value=\"$confirm\"/></div>
    <div>E-mail: <input type=\"text\" name=\"email\" value=\"$email\"/></div>
    <div>Confirm E-mail:<input type=\"text\" name=\"email2\" value=\"$email2\"/></div>
    <div><input type=\"submit\" value=\"Done\" name=\"submit\" /><a href=\"../lobby.php\"> Cancel</a></div>
</form>
</body>
</html>
			
			";
	echo $strForm;
}