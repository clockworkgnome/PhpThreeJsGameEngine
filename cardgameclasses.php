<?php
session_start();
set_time_limit (0);
require_once('inc/db.inc.php');

//get game request
$daRequest =$_REQUEST['gameRequest'];

class PlayingCard
{
    
    public $cost;//cost of card
    public $ccolor;//color of card
    public $cname;//card name
    public $tapped;// boolen if the card is used
    public $ctype;//card type
    public $rarity;// rarity of the card
    public $display; // this is the code to display a card
     
    //set functions*******************************************************
    public function setCName($cname){
        $this->cname = $cname;
    }
    public function setCColor($ccolor){
    	$this->ccolor = $ccolor;
    }
    public function setCost($cost){
        $this->cost = $cost;
    }
    public function setTapped($tapped){
        $this->tapped = $tapped;
    }
    public function setCType($ctype){
    	$this->ctype=$ctype;
    }
    public function setRarity($rarity){
    	$this->rarity=$rarity;
    }
    public function setDisplay($display){
    	$this->display = $display;
    }
    
    //get functions*****************************************************
    public function getTapped(){
        return $this->tapped;
    }
    public function getCColor() {
         return $this->ccolor;
    }
    public function getCost() {
         return $this->cost;
    }
    public function getCName() {
         return $this->cname;
    }
    public function getCType(){
    	return $this->ctype;
    }
    public function getRarity(){
    	return $this->rarity;
    }
    public function getDisplay(){
    	return $this->display;
    }
}

class Creature extends PlayingCard
{
    public $power;// power of the creature
    public $defence;// defence of the creature
    public $ability;//an array of creature abilities
    
        //the creature will have more power if less than the weight roll
        //the creature will have more defence if greater than the weight roll
        
        private $PvHPweight; //= mt_rand(1,10);// this is a marker on a line weight roll
        private $PvHProll; //= mt_rand(1,10);// this is the line
        private $pointPercent=.0;// = mt_rand(1,100)/100; //the percentage of points to be moved over
        private $bonusRoll1; //= mt_rand(1,6); //-1 cost but lose 1 points
        private $bonusRoll2; //= mt_rand(1,6); //-1 point but gain an ability
        private $bonusRoll3; //= mt_rand(1,6); //+1 cost but add an ability or 1 point
        private $whiteMod = 5;// increase white chances to be defencive
        private $blackMod = 5;//increase black chance to be offencive
        private $redMod = 2; //give red an advantage on bonus roll 1
        private $greenMod = 5;//give green an advantage on bonus roll 2
        private $points = 4.0;// set default points to 4
        private $pointsToMove=.0; //= $points*$pointPercdent;//number of pointto be moved to either power or defence
        private $pointsLeft=.0; //= $pointsToMOve-4;// number of points left
        public $rollArray;// an array of the rolls made for this creature
        
        private $rarityCheck; // if creature gets two or more bonous rolls it chages to uncommon
        
        
        
    function __construct($ccolor){// initialize creature by rolling and setting name and setting color
    	$this->setCColor($ccolor);
    	$this->setCType("creature");
    	$this->Roll();
        $this->makeName();
        //$this->makeDisplay();
    }   
         
    public function makeDisplay($cardName,$cardx,$cardy,$cardz){
    	$thisName=$this->getCName();
    	$thisPower=$this->getPower();
    	$thisDefence=$this->getDefence();
        $thisAbilities=$this-> getAbility();
        $thisColor=$this-> getCColor();
        $thisCost= $this-> getCost();
        $thisAbilityTextYpos = 300;
        //this will display the creatures power and defence 
    	$strCardRender="
    var dynamicTexture	= new THREEx.DynamicTexture(512,512)
	dynamicTexture.context.font	= \"bolder 40px Verdana\";
    dynamicTexture.drawText(\"$thisName\", 40, 32, 'gold');
    dynamicTexture.context.font	= \"bolder 40px Verdana\";
    dynamicTexture.drawText(\"$thisCost\", 450, 32, 'gold');
	dynamicTexture.context.font	= \"bolder 40px Verdana\"; 
    dynamicTexture.drawText(\"$thisPower / $thisDefence\", 240, 500, 'gold'); ";
    // this displays each of the creatures abilities on the card 
     foreach ($thisAbilities as $v) {
            $strCardRender= $strCardRender. " dynamicTexture.context.font = \"bolder 40px Verdana\"; "; 
            $strCardRender= $strCardRender. " dynamicTexture.drawText(\"$v[0]\", 40, $thisAbilityTextYpos, 'gold'); ";
            $thisAbilityTextYpos = $thisAbilityTextYpos +50;
        }
    	
        // this displays the images for the six sides of the box that makes the card 
        $strCardRender= $strCardRender." 
    	var materials = [
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/cardmid.png\"),side:THREE.DoubleSide,transparent: false}),
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/cardmid.png\"),side:THREE.DoubleSide,transparent: false}),
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/cardmid.png\"),side:THREE.DoubleSide,transparent: false}),
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/cardmid.png\"),side:THREE.DoubleSide,transparent: false}),
		new THREE.MeshBasicMaterial({map:dynamicTexture.texture,transparent: true,opacity:1.0, color: 0xcc8800}),
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/$thisColor.png\"),side:THREE.DoubleSide}),
		];
    	
    	var geometry = new THREE.BoxGeometry(10,16,0.1);
    	
    	
    	var cardBase = new THREE.Mesh(geometry, new THREE.MeshFaceMaterial(materials));
    	cardBase.name=\"$cardName\";
    	scene.add(cardBase);
    	cardBase.position.x = $cardx;
		cardBase.position.y = $cardy;
    	cardBase.position.z = $cardz;
    	"; 
    	return $strCardRender;
    } 
    
    
    private function Roll(){
        $this->cost=3; // set default creature cost to 3
        $this->ability= array(// sets this creatures normal abilities
        	array(0,"n"),// attack
        	array(1,"n"),// defend
        	);
        //roll dice
        $this->PvHPweight= mt_rand(1,10);
        $this->PvHProll= mt_rand(1,10);
        $this->pointPercent= mt_rand(1,99)/100;
        $this->bonusRoll1= mt_rand(1,6);
        $this->bonusRoll2= mt_rand(1,6);
        $this->bonusRoll3= mt_rand(1,6);
        $this->pointsToMove= $this->points*$this->pointPercent;
        $this->pointsLeft=4.0-$this->pointsToMove;
        $this->rarityCheck=0;
    
        
        //add color mods here
        $mycolor=$this->getCColor();
        
        if($mycolor=="white"){
             $this->PvHPweight= $this->PvHPweight-$this->whiteMod;
             //echo"i am white<br>";
        }
       elseif($mycolor=="black"){
            $this->PvHPweight= $this->PvHPweight+$this->blackMod;
            //echo"i am black<br>";
        }
       elseif($mycolor=="red"){
            $this->bonusRoll1 =  $this->bonusRoll1+$this->redMod;
        }
       elseif($mycolor=="green"){
           $this->bonusRoll2 = $this->bonusRoll2+$this->greenMod;
        }
        
        //add bonus roll 1 //-1 point but gain an ability or -1 cost
        if($this->bonusRoll1>=6){
        	$this->rarityCheck=$this->rarityCheck+1;
        	
            //echo "bonus1 won<br>";
            $decisionRoll= mt_rand(0,1);// decison to  -1 cost or ability
            $this->points=$this->points-1;
            if($decisionRoll==1){
                $this->cost =$this->cost-1;
            }else{
                //put ability fuction here
                //echo "c ability won<br>";
                $abilityRoll = mt_rand(0,3);
                array_push($this->ability,array($abilityRoll,"c"));
            }
        }
        
        //add bonus roll 2 //+1 cost but add an ability or 1 point
        if($this->bonusRoll2>=6){
        	$this->rarityCheck=$this->rarityCheck+1;
        	
            //echo "bonus2 won<br>";
            $decisionRoll= mt_rand(0,1);// decison to add ponit or ability
            $this->cost =$this->cost+1;
            if($decisionRoll==1){
                $this->points=$this->points+1;
            }else{
               //put ability fuction here
               //echo "uc ability won<br>";
                $abilityRoll = mt_rand(0,3);
                array_push($this->ability,array($abilityRoll,"uc"));
            }
            
        }
        
        //add bonus roll 3 //-1 cost and -2 points but gain an ability with more chance to be a good ability
        if($this->bonusRoll3>=6){
        	$this->rarityCheck=$this->rarityCheck+1;
        	
            //echo "bonus3 won<br>";
            $this->cost =$this->cost-1;
            $this->points=$this->points-2;
            
           //put ability fuction here
           //echo "r ability won<br>";
            $abilityRoll = mt_rand(0,3);
            array_push($this->ability,array($abilityRoll,"r"));
        }
        
        //chage rarity to uncommon if 2 or more bonus rolls are won else set to common
        //echo $this->getCName().",".$this->rarityCheck."<br>";
        if($this->rarityCheck>=2){
        	$this->setRarity("uc");
        }else{
        	$this->setRarity("c");
        }
        
        //sets power and toughness
        if($this->PvHPweight<$this->PvHProll){//the creature will have more defence if greater than the weight roll
            if($this->pointsToMove>$this->pointsLeft){
	        	$this->defence=$this->pointsToMove;
	            $this->power=$this->pointsLeft;
            }else{
            	$this->defence=$this->pointsLeft;
            	$this->power=$this->pointsToMove;
            }
        }else{//the creature will have more power if less than the weight roll
            if($this->pointsToMove>$this->pointsLeft){
            	$this->defence=$this->pointsLeft;
            	$this->power=$this->pointsToMove;
            }else{
            	$this->defence=$this->pointsToMove;
            	$this->power=$this->pointsLeft;
            }
        }
        
    }  
    
    public function getWeights(){
    	$tempStr = $this->PvHPweight.",".$this->PvHProll;
    	return $tempStr;
    }
    
    private function makeName(){
    	$tempName="Drone";
    	//set the name to the temp name
    	$this->setCName($tempName);
    }
    
    public function setPower($power){
        $this->power = $power;
    }
    public function setDefence($defence){
        $this->defence = $defence;
    }
    
    //sends ability code to be parsed into an array of abilities 
    public function getAbility(){
        $myAbilityList=$this->getAbilityList();
        $myAbilities=[];
        foreach ($myAbilityList as $v) {
            //echo "<br>Current value of \$myAbilities:".$v[0].",".$v[1]."<br>";
            array_push($myAbilities,$this->parseAbility($v[0],$v[1]));
        }
        return $myAbilities;
    }
    
    //get the array of abilities this crature has 
    private function getAbilityList(){
        return $this->ability;
    }
    
    //get a pase ability code to acctual ability array
    private function parseAbility($roll,$type){
    	//common ability
        //normal abilies all creatures have
        $normAbility = array(
        	array("Attack",0,true,"attack"),
        	array("Defend",0,false,"defend"),
        	);
        
        //common ability
        //no cost abilities that effect combat ex:flying, first strike, stelth
        $comAbility = array(
        	array("Flying",0,false,"flying"),
        	array("Stelth",0,false,"stelth"),
        	array("Quickness",0,false,"quickness"),
        	array("Reflex",0,false,"reflex"),
        	);
        
        //common ability
        //these abilities have a name, manacost,tap, effect
        $uncomAbility= array(
            array("Add Land",1,true,"addMana(1)"),
            array("Sear",1,true,"damage(1)"),
            array("protect",1,true,"protect(1)"),
            array("waste",1,true,"waste(1)"),
            );
            
        //common ability
        //these abilities have a name, manacost,tap, effect
        $rareAbility= array(
            array("Growth",1,true,"addPoints(2)"),
            array("Devastate",1,true,"damage(2)"),
            array("Soul Eater",0,true,"gainLife(1)"),
            array("Grave Digger",0,true,"creaturedig(1)"),
            );
        
        if($type=="n"){
            $tempHolder=array($normAbility[$roll][0],$normAbility[$roll][1],$normAbility[$roll][2],$normAbility[$roll][3]);
            return $tempHolder;
        }elseif($type=="c"){
            return $comAbility[$roll];
            $tempHolder=array($comAbility[$roll][0],$comAbility[$roll][1],$comAbility[$roll][2],$comAbility[$roll][3]);
            return $tempHolder;
        }elseif($type=="uc"){
            $tempHolder=array($uncomAbility[$roll][0],$uncomAbility[$roll][1],$uncomAbility[$roll][2],$uncomAbility[$roll][3]);
            return $tempHolder;
        }elseif($type=="r"){
            $tempHolder=array($rareAbility[$roll][0],$rareAbility[$roll][1],$rareAbility[$roll][2],$rareAbility[$roll][3]);
            return $tempHolder;
        }
        
    }
    
    public function getRolls() {//to view the random dice rolls for this creature
        $this->rollArray=["weightRoll"=>$this->PvHPweight,"line roll"=>$this->PvHProll,"pointPercent"=>$this->pointPercent,
                          "bounus1"=>$this->bonusRoll1,"bounus2"=>$this->bonusRoll2,"bounus3"=>$this->bonusRoll3,"pointsToMove"=>$this->pointsToMove,
                          "pointsLeft"=> $this->pointsLeft,];
        return $this->rollArray;
         
    }
    public function getPower() {
         return $this->power;
    }
    public function getDefence() {
         return $this->defence;
    }
    public function getAll(){// a culmination of what this creature is 
    	 $myCardVars = array($this->getCType(),$this->getCName(),$this->getCColor(),$this->getCost(),$this->getPower(),$this->getDefence(),$this->getAbility(),$this->getWeights(),$this->getRarity(),);
    	 return $myCardVars;
    }
            
}
class Mana extends PlayingCard// this is the energy used to cast creatures and spells 
{	
	function __construct($ccolor){// initialize mana
		$this->setCName(ucwords($ccolor." Mana"));
		$this->setCost(0);
		$this->setCColor($ccolor);
		$this->setMType($ccolor);
		$this->setCType("mana");
	
	}
	public function makeDisplay($cardName,$cardx,$cardy,$cardz){
		$thisName=$this->getCName();
		$thisColor=$this-> getCColor();

		$strCardRender="var dynamicTexture	= new THREEx.DynamicTexture(512,512)
	dynamicTexture.context.font	= \"bolder 40px Verdana\";
	dynamicTexture.drawText(\"$thisName\", 40, 30, 'gold');
		 
		var materials = [
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/cardmid.png\"),side:THREE.DoubleSide,transparent: false}),
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/cardmid.png\"),side:THREE.DoubleSide,transparent: false}),
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/cardmid.png\"),side:THREE.DoubleSide,transparent: false}),
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/cardmid.png\"),side:THREE.DoubleSide,transparent: false}),
		new THREE.MeshBasicMaterial({map:dynamicTexture.texture,transparent: true,opacity:1.0,color:0xcc8800}),
		new THREE.MeshBasicMaterial({map:new THREE.ImageUtils.loadTexture(\"img/$thisColor.png\"),side:THREE.DoubleSide}),
		];
		 
		var geometry = new THREE.BoxGeometry(10,16,0.1);
		 
		 
		var cardBase = new THREE.Mesh(geometry, new THREE.MeshFaceMaterial(materials));
		cardBase.name=\"$cardName\";
		scene.add(cardBase);
		cardBase.position.x = $cardx;
		cardBase.position.y = $cardy;
		cardBase.position.z = $cardz;
		 
		";
		return $strCardRender;
	}
	
	public function setMType($mType){
		$this->mType =$mType;// sets teh color of mana 
	}
	public function getMType(){
		return $this->mType;// get the color of mana 
	}
	public function getAll(){// a culmination of what this mana is
		$myCardVars = array($this->getCType(),$this->getCName(),$this->getCColor(),$this->getCost(),$this->getMType());
		//echo "i am here ".$myCardVars[0];
		return $myCardVars;
	}
}

class CardSet// a set of cards to build a deck from 
{
	public $cardSet=[];
	function __construct(){
		$this->buildIt();
	}
	public function buildIt(){
		
		//builds 40 black, white, green and red creatures and put them in the set
		for($i=0;$i<40;$i++){
			
			$this->cardSet[$i] = new Creature("black");
		}
		for($i=40;$i<80;$i++){
			
			$this->cardSet[$i] = new Creature("white");
		}
		for($i=79;$i<120;$i++){
			
			$this->cardSet[$i] = new Creature("green");
		}
		for($i=119;$i<160;$i++){
	
			$this->cardSet[$i] = new Creature("red");
		}
		
	}
	public function getCardSet(){
		return $this->cardSet;
	}
}

class Deck
{
	public $deck=[];// an array of cards in the deck 
	private $myCardSet;// a card set class
	private $myCards;// an array of cards from the card set
	private $endNumber;// the amount of cards in a set -1
	private $ranCard; // a random card form the set
	private $blackC;//count back cards
	private $whiteC;//count white cards
	private $greenC;//count green cards
	private $redC;//count red cards 
	private $multiplier;// a multipler to get the percent color of cards
	
	function __construct(){
		$this->buildIt();
	}
	public function buildIt(){
		$this->myCardSet= new CardSet();
		$this->myCards=$this->myCardSet->getCardSet();
		//var_dump($this->myCards);
		$this->endNumber = count($this->myCards)-1;
		
		//select 20 random cards from the card set for a deck
		for($i=0;$i<20;$i++){
			$this->ranCard = mt_rand(0,$this->endNumber);
			$this->deck[$i] = $this->myCards[$this->ranCard];
		}
		
		// calculate percent of each color card to set mana
		foreach ($this->deck as $v) {
			$tempColor = $v->getCColor();
			if($tempColor=="white"){
				$this->whiteC=$this->whiteC+1;
			}elseif($tempColor=="black"){
				$this->blackC=$this->blackC+1;
			}elseif($tempColor=="green"){
				$this->greenC=$this->greenC+1;
			}elseif($tempColor=="red"){
				$this->redC=$this->redC+1;
			}
		} 
		$this->multiplier = 100/count($this->deck);// to convert count to percent
		$this->whiteC=$this->whiteC*$this->multiplier;
		$this->blackC=$this->blackC*$this->multiplier;
		$this->greenC=$this->greenC*$this->multiplier;
		$this->redC=$this->redC*$this->multiplier;
		
		//percentages of each color card in the deck 
		$this->whiteC=$this->whiteC/100;
		$this->blackC=$this->blackC/100;
		$this->greenC=$this->greenC/100;
		$this->redC=$this->redC/100;
		//echo"<br> white:".$this->whiteC." black:".$this->blackC." green:".$this->greenC." red:".$this->redC."<br>";
		
		// calc amount of mana to add 
		$this->whiteC=round(10*$this->whiteC, 0, PHP_ROUND_HALF_UP);
		$this->blackC=round(10*$this->blackC, 0, PHP_ROUND_HALF_UP);
		$this->greenC=round(10*$this->greenC, 0, PHP_ROUND_HALF_UP);
		$this->redC=round(10*$this->redC, 0, PHP_ROUND_HALF_UP);
		//echo"<br> white:".$this->whiteC." black:".$this->blackC." green:".$this->greenC." red:".$this->redC."<br>";
		
		//add each color mana to the deck in correct proportions
		//white mana
		for($i=0;$i<$this->whiteC;$i++){
			$this->deck[]=new Mana("white");
		}
		//black mana
		for($i=0;$i<$this->blackC;$i++){
			$this->deck[]=new Mana("black");
		}
		//red mana
		for($i=0;$i<$this->redC;$i++){
			$this->deck[]=new Mana("red");
		}
		//green mana
		for($i=0;$i<$this->greenC;$i++){
			$this->deck[]=new Mana("green");
		}
		
	}
	public function getDeck(){
		return $this->deck;
	}
}
class cardGame
{
	// give the client the option to start their own game and wait for someone to join
	public function startNewGame($userID){
		//this makes a new game in data base
		$sql = 'INSERT INTO `jujugameengine`.`cardGames` (`id`, `status`, `player1`, `player2`) VALUES (NULL, \'looking\', \''.$userID.'\', \'\');';
        $GLOBALS['MySQL']->res($sql);
		// this sets player1 status to inque
		$sql = 'UPDATE `jujugameengine`.`s_members` SET `status` = \'inQue\' WHERE `s_members`.`id` = '.$userID.' LIMIT 1;';
		$GLOBALS['MySQL']->res($sql);
		// update session var with new status
		$sSQL = "SELECT status FROM `s_members` WHERE `id` = '".$userID."'";
		$_SESSION["sUserStatus"] = $GLOBALS['MySQL']->getOne($sSQL);
	}
	
	public function getCards(){
		// the following makes a deck shuffles it and then makes a hand of cards
		$playerHand=[];
		$newDeck = new Deck();
		$myDeck = $newDeck->getDeck();
		//var_dump($myDeck);
		$i=0;
		shuffle($myDeck);
		for($i=0;$i<7;$i++){
			array_push($playerHand,$myDeck[$i]);
			unset($myDeck[$i]);
		}
		//make this this players deck and hand in the session
	
		$_SESSION["myDeck"]=$myDeck;
		$_SESSION["myHand"]=$playerHand;
		//set deck and hand in the database
		$daGameId=$_SESSION["gameID"];
		$iUserID = $_SESSION["sUserID"];
		$myDeck=json_encode($myDeck);
		$playerHand=json_encode($playerHand);
		//$_SESSION["playerNum"]==2 mean this player is player 2
		$sql = 'INSERT INTO `jujugameengine`.`cardGameInfo` (`gameId`, `status`, `hand`, `deck`, `graveyard`, `inplay`, `life`, `mana`, `playerId`, `needsUpdate`) VALUES (\''.$daGameId.'\', \'Started\', \''.$playerHand.'\', \''.$myDeck.'\', \'grave\', \'inplay\', \'20\', \'mana\', \''.$iUserID.'\',\'false\');';
		$GLOBALS['MySQL']->res($sql);
	}
}

//*******************************************************************
// if lobby requested a new game be made
//*******************************************************************
if($daRequest=="makeGame"){
	//if user logged in do stuff
	if ($_SESSION["bLoggedIn"]) {

		//get the users id
		$iUserID = $_SESSION["sUserID"];

		//get the users status
		$sSQL = "SELECT status FROM `s_members` WHERE `id` = '".$iUserID."'";
		$_SESSION["sUserStatus"] = $GLOBALS['MySQL']->getOne($sSQL);
		$iUserStatus=$_SESSION["sUserStatus"];
		
		// if the user is not  in que
		if($iUserStatus!="inQue"){

		//this makes a new game in the database and set user status to inQue
		$myGame = new cardGame;
		$myGame->startNewGame($iUserID);

		//this display the status change
		$strLook="You are now in que...<br>";
		echo $strLook;
		}
	}

}
//****************************************************************************
// if game client request the game begin 
//****************************************************************************
if($daRequest=="getCards"){
	//if user logged in do stuff
	if ($_SESSION["bLoggedIn"]) {
		
		//make a new card game class
		$myGame = new cardGame;
		//make the players deck and first hand of cards 
		$myGame->getCards();
		//send script to add deck vissual and remove strt button
		$myScript="
			<script>
			scene.remove( startButton );
            scene.add(deckVis);
            deckVis.position.x = 48;   
            deckVis.position.y = -5;
			</script>
				
				";
		echo $myScript;
		//set players game status to started and needs update
		$sql = 'UPDATE `jujugameengine`.`cardGameInfo` SET `status` = \'showDeck\', `needsUpdate` = \'true\' WHERE `cardGameInfo`.`playerId` = \''.$_SESSION["sUserID"].'\'';
		$GLOBALS['MySQL']->res($sql);
	}
	
}
//********************************************************************
//if the game Request draw hand 
//********************************************************************
if($daRequest=="drawHand"){
	
	//if user logged in do stuff
	if ($_SESSION["bLoggedIn"]) {
		//get the game id
		$daGameId=$_SESSION["gameID"];
		
		$sSQL = "SELECT status FROM `cardGameInfo` WHERE `playerId` = '".$_SESSION["sUserID"]."' AND `gameId` = '".$daGameId."'";
		$_SESSION["myStatus"] = $GLOBALS['MySQL']->getOne($sSQL);
		
		if($_SESSION["myStatus"]!=false){
			
			if($_SESSION["myStatus"]=="showDeck"){
				//set players game status to handDrawn needs update to true
				$sql = "UPDATE `jujugameengine`.`cardGameInfo` SET `status` = 'handDrawn', `needsUpdate` = 'true' WHERE CONVERT(`cardGameInfo`.`playerId` USING utf8) = '".$_SESSION["sUserID"]."' LIMIT 1;";
				$GLOBALS['MySQL']->res($sql);
				
				echo "<script>";
				
				$playerHand=$_SESSION["myHand"];
				$cardX = -5;
				$cardz = 0;
				$i=0;
				//var_dump($playerHand);
				foreach ($playerHand as $v) {
				//var_dump($v);
				//$DisplayCode=$v->makeDisplay("hand$i",$cardX,-5);
				$DisplayCode=$v->makeDisplay("hand$i",$cardX,-5,$cardz);
				
				echo $DisplayCode;
				$cardX=$cardX+7;
				$cardz=$cardz+0.5;
				$i=$i+1;
				}
				echo "</script>";
			}
		}
		
	}
}

?>