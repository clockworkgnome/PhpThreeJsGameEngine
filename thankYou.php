<?php
session_start();

set_time_limit (0);

require_once('inc/db.inc.php');
            // this gets the users id to logg out
		$iUserID=$_SESSION["sUserID"];
		
		// remove game data from game currently in play
		if(isset($_SESSION["gameID"])){
			//get the game id
			$daGameId=$_SESSION["gameID"];
			
			$sql ="DELETE FROM `cardGameInfo` WHERE `cardGameInfo`.`gameId`='".$daGameId."'";
			$GLOBALS['MySQL']->res($sql);
		}
        	//remove games from lobby when user logs out
        	$sql ="DELETE FROM `cardGames` WHERE `cardGames`.`player1`='".$iUserID."'";
            $GLOBALS['MySQL']->res($sql);
            $sql ="DELETE FROM `cardGames` WHERE `cardGames`.`player2`='".$iUserID."'";
            $GLOBALS['MySQL']->res($sql);
        	
        	//set user offline in database
        	$sql = 'UPDATE `jujugameengine`.`s_members` SET `status` = \'offline\' WHERE `s_members`.`id` = '.$iUserID.' LIMIT 1;';
            $GLOBALS['MySQL']->res($sql);

// kill the session 
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    session_regenerate_id(true);

?>

<html>
<head></head>
<body>
thank you for playing comeback soon.<br>
<a href="index.php">login</a>
</body>
</html>