<?php
session_start();
set_time_limit (0);
require_once('inc/db.inc.php');

//var_dump($_REQUEST);
//get lobby request
$daRequest =$_REQUEST['lobbyRequest'];

// if the user clicked join game 
if($daRequest=="joinGame"){
	//if user logged in do stuff
	if ($_SESSION["bLoggedIn"]) {
		//if the user is not inQue making their own game
		if($_SESSION["sUserStatus"]!="inQue"){
			// get the user id 
			$iUserID = $_SESSION["sUserID"];
			// get the game id to join 
			$daGameId =$_REQUEST['gameID'];
			// set player two equal to this user and set the games status to found 
			$sql = 'UPDATE `jujugameengine`.`cardGames` SET `status` = \'found\', `player2` = \''.$iUserID.'\' WHERE `cardGames`.`id` = '.$daGameId.' LIMIT 1;';
			$GLOBALS['MySQL']->res($sql);
			// this sets player2 status to inGame
			$sql = 'UPDATE `jujugameengine`.`s_members` SET `status` = \'inGame\' WHERE `s_members`.`id` = '.$iUserID.' LIMIT 1;';
			$GLOBALS['MySQL']->res($sql);
			//set userstatus in session
			$sSQL = "SELECT status FROM `s_members` WHERE `id` = '".$iUserID."'";
			$_SESSION["sUserStatus"] = $GLOBALS['MySQL']->getOne($sSQL);
			//put the game id in a session var
			$_SESSION["gameID"]=$daGameId;
			$_SESSION["playerNum"]=2;
			// send the user to the connecting page 
			header("Location: linkUp.php");
		}
	
	}
}


// this displays default content of the lobby
if($daRequest=="getContent1"){
//if user logged in do stuff
	if ($_SESSION["bLoggedIn"]) {
		
		//make a new lobby
		$myLobby = new lobby;
		$onlinePlayers = $myLobby->getOnlinePlayers();
		
		//gets online players and displays them
		echo "<div id=\"onlinePlayers\">";
		foreach ($onlinePlayers as $v) {
			echo $v["name"]." is online<br>";
			//var_dump($v);
		}
		echo "</div>";
		echo "<div id=\"currentGames\">";
		
		//get the users id
		$iUserID = $_SESSION["sUserID"];
		//get the users status
		$sSQL = "SELECT status FROM `s_members` WHERE `id` = '".$iUserID."'";
		$iUserStatus= $GLOBALS['MySQL']->getOne($sSQL);
		//echo $iUserStatus;
		//if user is inque display an in game message
		if($iUserStatus=="inQue"){
			echo "you are currently making a game...<br>";
			//get the users id
			$iUserID = $_SESSION["sUserID"];
			
			//get game id if game status=found this means a second player joined
			$sSQL = "SELECT `id` FROM `cardGames` WHERE `player1`='".$iUserID."' AND `status`='found'";
			$gameID = $GLOBALS['MySQL']->getOne($sSQL);
			//var_dump($gameID);
			if($gameID==false){
				echo "looking for player two ...";
			}else{
				$_SESSION["gameID"]=$gameID;
				$_SESSION["playerNum"]=1;
				// this sets player1 status to inGame
				$sql = 'UPDATE `jujugameengine`.`s_members` SET `status` = \'inGame\' WHERE `s_members`.`id` = '.$iUserID.' LIMIT 1;';
				$GLOBALS['MySQL']->res($sql);
				//set userstatus in session
				$sSQL = "SELECT status FROM `s_members` WHERE `id` = '".$iUserID."'";
				$_SESSION["sUserStatus"] = $GLOBALS['MySQL']->getOne($sSQL);
				echo"<script>myStopFunction();</script>Found player two. Start game?<a href=\"linkUp.php\"><img src=\"img/StartGame.png\" class=\"clickables\"></a><br>";
			}
		}elseif($iUserStatus=="online"){
			// the user is online and not in que or in a game so offer games to join
				
			//this statment selects all games this player did not make and are currently looking for a player two
			$sSQL = "SELECT * FROM `cardGames` WHERE `player1`<>'".$iUserID."' AND `status`='looking'";
			$currentGames=$GLOBALS['MySQL']->getAll($sSQL);
			//var_dump($currentGames);
			if(count($currentGames)>0){
				foreach ($currentGames as $v) {
					$sSQL = "SELECT name FROM `s_members` WHERE `id` = '".$v["player1"]."'";
					$thisName= $GLOBALS['MySQL']->getOne($sSQL);
					echo $thisName." would like to start a game<a href=\"lobbyClasses.php?lobbyRequest=joinGame&gameID=".$v["id"]."\"><img src=\"img/JoinGame.png\" class=\"clickables\"></a><br>";
				}
			}else{
				// there was no games 
				echo "no open games.";
			}
		}
		echo "</div>";
				
	}else{
		echo "You need to be logged in to do this.";
	}
}


// start the lobby class
class lobby{
	
	//get list of clients online
	public function getOnlinePlayers(){
		$sSQL = "SELECT name FROM `s_members` WHERE `status` <> 'offline'";
		$names = $GLOBALS['MySQL']->getAll($sSQL);
		return $names;
	}
		
}
?>