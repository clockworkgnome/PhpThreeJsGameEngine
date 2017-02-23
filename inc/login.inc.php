<?php
session_start();
require_once('lib/password.php');

class SimpleLoginSystem {
   function getLoginBox() {

        $sLoginForm = "
    <form class=\"login_form\" method=\"post\" action=\"\">
    <div>Username: <input type=\"text\" name=\"username\" /></div>
    <div>Password: <input type=\"password\" name=\"password\" /></div>
    <div><input type=\"submit\" value=\"Login\" name=\"Login\" /><a href=\"inc/noob.php\"> New Account</a></div>
	</form>
        		
        		";
		
        // if the user entered a user name and password and submitted
        if ($_REQUEST['username'] && $_REQUEST['password']) {
        	// check credentials 
            if ($this->check_login($_REQUEST['username'], $_REQUEST['password'])) {
            	//creds are good memeber info is set set $_SESSION["bLoggedIn"] = true;
                $_SESSION["bLoggedIn"] = true;
				// go to lobby to join games 
                header("Location: lobby.php"); exit;
            } else {
            	//creds bady get form again
                $_SESSION["bLoggedIn"] = false;
                echo 'Username or Password is incorrect<br>' . $sLoginForm;
            }
        }else {
        	//display the login box 
            echo $sLoginForm;
        }
    }

    function check_login($sName, $sPass) {
    	
    	//get sql safe user name
        $sNameSafe = $GLOBALS['MySQL']->process_db_input($sName, A_TAGS_STRIP);
        
		//try to get hash
        $sSQL = "SELECT `pass` FROM `s_members` WHERE `name`='{$sNameSafe}'";
        $hash = $GLOBALS['MySQL']->getOne($sSQL);
		if($hash!=false){
			//user name good check password
	        if(password_verify($sPass , $hash)){
	        	// password good get member id
	        	$sSQL = "SELECT `id` FROM `s_members` WHERE `name`='{$sNameSafe}'";
	        	$iID = $GLOBALS['MySQL']->getOne($sSQL);
                // uses member ID to set user info in session 
	        	$this->setLoggedMemberInfo($iID);
	        	
                // set user status to online in database 
	        	$iUserID = $_SESSION["sUserID"];
	        	$sSQL = "SELECT status FROM `s_members` WHERE `id` = '".$iUserID."'";
	        	$iStatus = $GLOBALS['MySQL']->getOne($sSQL);
	        	if($iStatus =="offline"){
		        	$sql = 'UPDATE `jujugameengine`.`s_members` SET `status` = \'online\' WHERE `s_members`.`id` = '.$iUserID.' LIMIT 1;';
		        	$GLOBALS['MySQL']->res($sql);
	        	}
	        	
	        	return true;
	        }else{
	        	//bad password
	        	echo "wrong password $sPass $hash<br>";
                 $_SESSION["bLoggedIn"] = false;
	        	return false;
	        }
		}else{
			//wrong username
			echo "wrong user $sNameSafe<br>";
             $_SESSION["bLoggedIn"] = false;
			return false;
		}
    
    }

    function setLoggedMemberInfo($iMemberID) {
        $sSQL = "SELECT * FROM `s_members` WHERE `id`='{$iMemberID}'";
        $aMemberInfos = $GLOBALS['MySQL']->getAll($sSQL);
        $GLOBALS['aLMemInfo'] = $aMemberInfos[0];
        //$sUsername = $GLOBALS['aLMemInfo']['name'];
        //$sUserID = (int)$GLOBALS['aLMemInfo']['id'];
        $_SESSION["sUsername"]=$GLOBALS['aLMemInfo']['name'];
        $_SESSION["sUserID"]=(int)$GLOBALS['aLMemInfo']['id'];
    }
}

$GLOBALS['oSimpleLoginSystem'] = new SimpleLoginSystem();

?>