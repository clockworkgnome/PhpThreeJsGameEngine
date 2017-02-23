<?php
session_start();
require_once('inc/db.inc.php');

// if the user is logged in
if ($_SESSION["bLoggedIn"]) {

	//get the users id
	$iUserID = $_SESSION["sUserID"];

	//get the users status
	$sSQL = "SELECT status FROM `s_members` WHERE `id` = '".$iUserID."'";
	$_SESSION["sUserStatus"] = $GLOBALS['MySQL']->getOne($sSQL);
}
?>
<html>
    <head>
		<title>My first Three.js app</title>
<style>
body {
    background-color: #d0e4fe;
}

h1 {
    color: orange;
    text-align: center;
}

p {
    font-family: "Times New Roman";
    font-size: 20px;
}

.clickables
{
    cursor: pointer;
}
</style>
	</head>
	<body>
<script src='js/jquery-2.1.3.min.js'></script>
<script>
// button code ****************************************************************************************
$(document).ready(function(){
	//the user clicke make game so make a game 
    $("#makeGame").click(function(){
    	// this makes a new game 
    	function getGame() {
        	//clear make game button
	        $("#makeGameContainer").html("In Que...");
	        $("#lobbyContent").html("Getting Data...");
	        // send the post to make a game
	    	$.post("cardgameclasses.php",
				    {
				        gameRequest: "makeGame",
				    },
				    function(data, status){
				        //alert("Data: " + data + "\nStatus: " + status);
				        if(status == "success"){
				        	$("#playerStatus").html(data);
				        }else{
				        	$("#playerStatus").html("failed...");
				        }
				    });
    	}
    	getGame();

    });

    //logg user out
    $("#logout").click(function(){
		//stop the loop
    	myStopFunction();
    	$("#playerStatus").html("Logging Out...");
        $("#lobbyContent").html("Logging Out...");
        $("#makeGameContainer").html("Logging Out...");
        //go to log out page
    	window.location.href='thankYou.php';
    	
    });
    
    
});

</script>

<?php 
if ($_SESSION["bLoggedIn"]) {
	echo'<div id="signIn"><img src="img/LogOut.png" class="clickables" id="logout"></div>';

}else{
	echo'<div id="signIn"><a href="index.php">Login</a></div>';
}
?>
<div id="scripts">
<script>
//loop code that gets default content
function myTimer() {
	$.post("lobbyClasses.php",
	{
		lobbyRequest: "getContent1",
	},
	function(data, status){
		if(status == "success"){
			$("#lobbyContent").html(data);
		}else{
			$("#lobbyContent").html("failed...");
		}
	});
}
myTimer();
var myVar = setInterval(function(){ myTimer() }, 5000);

function myStopFunction() {
    clearInterval(myVar);
}
</script>
</div>
<div id="playerStatus">
<?php 
if ($_SESSION["bLoggedIn"]) {
	echo $_SESSION["sUserStatus"];
}
?>
</div>
<div id="lobbyContent"></div>
<div id="makeGameContainer">
<img src="img/NewGame.png" class="clickables" id="makeGame">
</div>
</body>
</html>