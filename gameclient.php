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
		<title>Game Client</title>
		<style>
		body {
		    background-color: #d0e4fe;
		    margin: 0;
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

		canvas { width: 100%; height: 100% }
		</style>
		
	</head>
	<body> 
		<script src="js/three.min.js"></script>
		<script src='js/threex.dynamictexture.js'></script>
		<script src='js/jquery-2.1.3.min.js'></script>
		
		<script>
//****************************************************************************************************************
//this loop will updates what the other player is doing 
//****************************************************************************************************************
			function myTimer() {
				$.post("gameUpdates.php",
				{
					gameRequest: "getUpdates",
				},
				function(data, status){
					if(status == "success"){
						$("#playerUpdates").html(data);
					}else{
						$("#playerUpdates").html("failed...");
					}
				});
			}
			myTimer();
			var myVar = setInterval(function(){ myTimer() }, 5000);
			
			function myStopFunction() {
			    clearInterval(myVar);
			}
//***************************************************************************************************************
			// this is the main scene for the card game
			var scene = new THREE.Scene();
			
			// the following sets up an orthographic camera for rendering the scene
			var viewsize=50;
			var canvasWidth = window.innerWidth;
			var canvasHeight = window.innerHeight;
			var aspectRatio =canvasWidth/canvasHeight;  
			camera = new THREE.OrthographicCamera( -aspectRatio*viewsize/2, aspectRatio*viewsize/2, viewsize/2, -viewsize/2, -1000, 1000 );
				camera.position.x = 20;
				camera.position.y = 10;
				camera.position.z = 10;  
				
            // this is a renderer for our scene
			var renderer = new THREE.WebGLRenderer();
			renderer.setSize( window.innerWidth, window.innerHeight );
			document.body.appendChild( renderer.domElement );

//this handles window resizing
window.addEventListener( 'resize', onWindowResize, false );

function onWindowResize(){
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize( window.innerWidth, window.innerHeight );
}
//******************************************************************************************************************
//this is the code for the deck vissual
//******************************************************************************************************************
            //the following sets up dynamic text to display on the deck vissual
            var deckText	= new THREEx.DynamicTexture(512,512)
	        deckText.context.font	= "bolder 40px Verdana";
		    deckText.drawText("Deck", 40, 60, 'white')
            //dynamicTexture.drawText("World", 40, 500, 'white')  
            
            //the following is the materials is used for the deck visual
		    var materials = [  
							new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture("img/cardmid.png"),side:THREE.DoubleSide}),
		         			new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture("img/cardmid.png"),side:THREE.DoubleSide}),
		         			new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture("img/cardmid.png"),side:THREE.DoubleSide}),
		         			new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture("img/cardmid.png"),side:THREE.DoubleSide}),
		         			new THREE.MeshBasicMaterial({map:deckText.texture,transparent: true,opacity:0.5}),
		         			new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture("img/cardmid.png"),side:THREE.DoubleSide}),
		                     ];
            //this creates the geometry cordinates for the deck visual
            var geometry = new THREE.BoxGeometry(10,16,0.1);  

            //this creates the mesh for the deck vissual
            var deckVis = new THREE.Mesh(geometry, new THREE.MeshFaceMaterial(materials));
            deckVis.name="GUIDeck";
            
//*********************************************************************************************************
//this is the code for the start button
//*********************************************************************************************************

// this makes the material and geometry for the start button and adds it to the scene
var material=new THREE.MeshBasicMaterial({map: THREE.ImageUtils.loadTexture( 'img/start.png' )})
var geometry = new THREE.PlaneGeometry( 20, 5,5 );
var startButton = new THREE.Mesh( geometry, material );
startButton.name="GUIstartButt";
scene.add( startButton );

//this positions the start button to the middle of the screen 
startButton.position.x = 20;   
startButton.position.y = -5;

//**************************************************************************************************************
//This is code for a mouse events
//**************************************************************************************************************
var mouse = new THREE.Vector2();
raycaster = new THREE.Raycaster();
//**********************
// this is to set the mesh back to its original x,y,z position after it has been brought forward after mouse over
var oldZposition =0;
var oldXposition =0;
var oldYposition =0;
//**********************
document.addEventListener( 'click', onDocumentMouseClick, false );
document.addEventListener( 'mousemove', onDocumentMouseOver, false );
var newSelected;
function onDocumentMouseOver( event ) {
    
    event.preventDefault();

	mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
	mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;

	// find intersections

	raycaster.setFromCamera( mouse, camera );
	var intersects = raycaster.intersectObjects( scene.children );
    if( intersects.length > 0 ){// moused over object
    	var intersect	= intersects[ 0 ];
    	if(newSelected!=null){// reset last moused over object even if never moused out
            newSelected.scale.set(1,1,1);
            newSelected.position.z = oldZposition;
            newSelected.position.y = oldYposition;
            
            newSelected = null;
            }
		newSelected	= intersect.object;
		//save the old position of the selected object
		oldZposition= newSelected.position.z;
		oldYposition= newSelected.position.y;
		
		console.log('old z position', oldZposition)
		console.log('old y position', oldYposition)
		//if the mouse is not over a gui element
		if(newSelected.name.indexOf("GUI")<0){
			console.log('you are over on mesh', newSelected)
	        newSelected.scale.set(2,2,2);
	        newSelected.position.z = 9;
	        newSelected.position.y = newSelected.position.y +8;  
		}
        
    }else{
    // mouse out
        if(newSelected!=null){// reset moused over object
        newSelected.scale.set(1,1,1);
        newSelected.position.z = oldZposition;
        newSelected.position.y = oldYposition;  
        newSelected = null;
        }
    }
}

function onDocumentMouseClick( event ) {

	event.preventDefault();

	mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
	mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;

	// find intersections

	raycaster.setFromCamera( mouse, camera );

	var intersects = raycaster.intersectObjects( scene.children );
	

	if( intersects.length > 0 ){
// if an object was clicked
// to tap a card that was clicked newSelected.rotation.z = -1.57;
		var intersect	= intersects[ 0 ];
		var newSelected	= intersect.object;
		//console.log('you clicked on mesh', newSelected)
//***********************************************************************************************
// if the user clicked the start button
//***********************************************************************************************
		if(newSelected.name=='GUIstartButt'){
			//this will make the players deck and hand then put them in a session vars called 
			//$_SESSION["myDeck"]=$myDeck;
			//$_SESSION["myHand"]=$playerHand;
			$.post("cardgameclasses.php",
				    {
						gameRequest: "getCards",
				    },
				    function(data, status){
				        //alert("Data: " + data + "\nStatus: " + status);
				        if(status == "success"){
				        	//$.getScript("cardgameclasses.php");
				        	// this script will remove the start button and add the deck vissual 
				        	$("#GameCommand").html(data);
				        }
				    });

		}
//*******************************************************************************************************
// the user clicked the deck
//*******************************************************************************************************
		if(newSelected.name=='GUIDeck'){
			//this will make the players deck and hand then put them in a session vars called 
			//$_SESSION["myDeck"]=$myDeck;
			//$_SESSION["myHand"]=$playerHand;
			$.post("cardgameclasses.php",
				    {
						gameRequest: "drawHand",
				    },
				    function(data, status){
				    	//alert("Data: " + data + "\nStatus: " + status);
				        if(status == "success"){
				        	//$.getScript("cardgameclasses.php");
				        	// this script will remove the start button and add the deck vissual 
				        	$("#GameCommand").html(data);
				        }
				    });
		}
//*********************************************************************************************************
	}else{
		//console.log('you clicked nothing')
	}


}

//***************************************************************************************************************
// this is the render loop
var render = function () {
				
requestAnimationFrame( render );
//cardBase.rotation.y += 0.01;
renderer.render(scene, camera);
				
};

render();
		</script>
<div id="GameCommand"></div>
<div id="playerUpdates"></div>
	</body>
</html>