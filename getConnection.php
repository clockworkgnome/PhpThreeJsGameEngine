<?php 
session_start();
require_once('inc/db.inc.php');


					$iUserID = $_SESSION["sUserID"];
					//get game id if game status=started this means player one joined so start game 
					$sSQL = "SELECT `id` FROM `cardGames` WHERE `player2`='".$iUserID."' AND `status`='started'";
					$gameID = $GLOBALS['MySQL']->getOne($sSQL);
					//var_dump($gameID);
					if($gameID==false){
						echo "connecting to game please wait ...";
					}else{
						// make sure game id has not been modified 
						if($gameID==$_SESSION["gameID"]){
							// this will send user to the client
							echo"done.";
						}
					}
					
				