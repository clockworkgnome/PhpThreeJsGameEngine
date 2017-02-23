<?php 
session_start();
require_once('inc/db.inc.php');

				if($_SESSION["playerNum"]==2){
					
					$strMyScript="
						function myTimer() {
							$.post(\"getConnection.php\",
							{
								daRequest: \"getContent\",
							},
							function(data, status){
								if(status == \"success\"){
									if(data==\"done.\"){
										window.location.href='gameclient.php';
									}else{
										$(\"#myContent\").html(data);
									}
								}else{
									$(\"#myContent\").html(\"failed...\");
								}
							});
						}
						myTimer();
						var myVar = setInterval(function(){ myTimer() }, 5000);
							
							";
					
					echo"<html><head></head><body><script src='js/jquery-2.1.3.min.js'></script><script>";
					echo $strMyScript;
					echo"</script><div id=\"myContent\"></div></body></html>";
					
				}
				// if this is the first player
				if($_SESSION["playerNum"]==1){
					//set game to started 
					$daGameId=$_SESSION["gameID"];
					$sql = 'UPDATE `jujugameengine`.`cardGames` SET `status` = \'started\' WHERE `cardGames`.`id` = '.$daGameId.' LIMIT 1;';
					$GLOBALS['MySQL']->res($sql);
					// send user to client 
					header("Location: gameclient.php");
				}




?>