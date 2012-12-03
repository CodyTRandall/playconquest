<?php

/**************
BUILD.PHP
Main building page for planets
@param - id, the id of the planet they are examining
**************/

//Include header
include('./header.php');

//Make sure they are on a planet
if(isset($_GET['id'])){
  $pid = escape_data($_GET['id']);
}else{
  exit();
}

//Check if session id exists
if(!isset($sid)){
  exit();
}

//Define which table is shown first.
echo '<SCRIPT LANGUAGE="JavaScript">var changer=2</SCRIPT>';
function setStart($x){
  echo '<SCRIPT LANGAUAGE="JavaScript">changer='.$x.'</SCRIPT>';
}

/*******************
MESSAGE HANDLING
Each of the following handles the appropriate message being passed to this page.
********************/
/*************
This prints the error message for moving planet since it is handled by planetdetails
**************/
if(isset($_GET['err'])){
  setStart(1);
  $err = (integer) escape_data($_GET['err']);
  if($err == 1){
    echo 'You have sent your fleet<br><br>';
  }else if($err == 2){
    echo 'You can not move an empty fleet.<br><br>';
  }else if($err == 3){
    echo 'It is impossible for that fleet to reach this System.<br><br>';
  }
}

function galaxyControlled($sid,$pid){
  $z = getPlanetStat(z,$sid,$pid);
  $query = "SELECT ownerpid FROM galaxy$sid WHERE id=$z";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  if($row[0] == 0){
    return false;
  }
  return true;
}

//Select planet variables
$query = "SELECT name,ownerid,landused,pleximod,steelmod,civmod,researchmod FROM planets$sid WHERE id=$pid";
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);

if($row[ownerid]!=$id){
  echo 'You do not own this planet.';
  include('./footer.php');
  exit();
}

/*********
Check if player is trying to use merchant port
***********/
if(isset($_GET['mid'])){
  setStart(5);
  
    $go=true;
    $mpid = (integer) $_GET['mid'];
    $mcivs = max(0,((integer) escape_data($_POST['mcivs'])));
    $msteel = max(0,((integer) escape_data($_POST['msteel'])));
    $mcylite = max(0,((integer) escape_data($_POST['mcylite'])));
    $mplexi = max(0,((integer) escape_data($_POST['mplexi'])));
    
    //Check if owner
    if($id != getPlanetStat(ownerid,$sid,$mpid)){
      echo 'err';
      exit();
    }
    
    //Check if valid resources
    $steel = getPlanetStat(steel,$sid,$mpid);
    $steel -= $msteel;
    if($steel<0){
      echo 'You do not have enough Elinarium.<br>';
      $go = false;
    }
    $civs = getPlanetStat(civs,$sid,$mpid);
    $civs -= $mcivs;
    if($civs<0){
      echo 'You do not have enough Civs.<br>';
      $go = false;
    }
    $cylite = getPlanetStat(cylite,$sid,$mpid);
    $cylite -= $mcylite;
    if($cylite<0){
      echo 'You do not have enough Cylite.<br>';
      $go = false;
    }
    $plexi = getPlanetStat(plexi,$sid,$mpid);
    $plexi -= $mplexi;
    if($plexi<0){
      echo 'You do not have enough Plexi.<br>';
      $go = false;
    }
    
    //Check if they can travel to the planet
    $traveltime = travelTime($sid,$mpid,$pid,$id);
    if($traveltime<0){
      echo 'The merchants can not reach the planet.<br>';
      $go=false;
    }
    
    $mod = getPlanetStat(merchant,$sid,$pid);
    $mod = .89+($mod*.01);
    $mcivs = floor($mcivs*$mod);
    $msteel = floor($msteel*$mod);
    $mcylite = floor($mcylite*$mod);
    $mplexi = floor($mplexi*$mod);
    
    //If go
    if($go){
      //Update planet resources
      $query = "UPDATE planets$sid SET steel=$steel,plexi=$plexi,civs=$civs,cylite=$cylite WHERE id=$mpid";
      $result = @mysql_query($query);
      $query = "INSERT INTO merchants$sid(ownerid,did,civs,steel,cylite,plexi,arrivaltime) VALUES($id,$pid,$mcivs,$msteel,$mcylite,$mplexi,$traveltime)";
      $result = @mysql_query($query);
      echo 'The merchant has left to pick up your resources.<br>';
    }
    echo '<br>';
}

/*************
Check if building was to be cancelled
************/
if(isset($_GET['a'])){

  $a = (integer) escape_data($_GET['a']);
  
  if($a==0){
  
    //Check that the planet is building something
    $cid = getPlanetStat(constructionid,$sid,$pid);
    if($cid == 0){
      echo 'err';
      exit();
    }
    
    //Refund the land
    $land = getBuildStat(landcost,$cid);
    $land += getPlanetStat(land,$sid,$pid);
    
    //Set the planet stats
    setPlanetStat(constructionid,0,$sid,$pid);
    setPlanetStat(land,$land,$sid,$pid);
    setPlanetStat(constructiontime,0,$sid,$pid);
  }
}

/************
Check if building is being destroyed
*************/
if(isset($_GET['d'])){
  
  $d = (integer) escape_data($_GET['d']);
  
  if($d==8){
    echo 'err';
    exit();
  }
  //Make sure you have enough steel
  $steel = getPlanetStat(steel,$sid,$pid);
  $steel -= 2500;
  if($steel<0){
    echo 'You do not have enough Elinarium to pay for the destruction.';
  }else{
    $go=true;
    
    $type = getBuildStat(increases,$d);
    //Do the checks for biodome
    if($type=='maxcivs'){
      $maxciv = getPlanetStat(maxcivs,$sid,$pid);
      if($maxciv-50<100){
        echo 'You can not destroy any more Biodomes.<br><br>';
        $go=false;
      }else{
        $maxciv-=50;
        setPlanetStat(maxcivs,$maxciv,$sid,$pid);
        $civs = getPlanetStat(civs,$sid,$pid);
        $civs = min($civs,$maxciv);
        setPlanetStat(civs,$civs,$sid,$pid);
      }
    }else{
      $num = getPlanetStat($type,$sid,$pid);
      $num -= getBuildStat(increaseamt,$d);
      if($num<getBuildStat(basenumber,$d)){
        echo 'You can not lower this below its base number.<br><br>';
        $go=false;
      }else{
        setPlanetStat($type,$num,$sid,$pid);
      }
    }
    if($go){
      echo 'You have destroyed a '.getBuildStat(name,$d).'.<br><br>';
      $land = getPlanetStat(land,$sid,$pid);
      $land += getBuildStat(landcost,$d);
      setPlanetStat(steel,$steel,$sid,$pid);
      setPlanetStat(land,$land,$sid,$pid);
    }
  }
}
    
    
/***********
JAVASCRIPT FOR CONFIRM CANCEL BOX
************/
echo '
  <SCRIPT LANGAUGE="JavaScript">
    function confirmPost(){
      var agree=confirm("Are you sure you wish to cancel this building? Only land is refunded");
      if(agree){
        window.location = "http://www.playconquest.com/build.php?id='.$pid.'&a=0";
      }
    }
    function confirmBuilding(id){
      var agree=confirm("Are you sure you wish to destroy this building? It costs 2500 Elinarium and only land is refunded.");
      if(agree){
        window.location = "http://www.playconquest.com/build.php?id='.$pid.'&d="+id;
      }
    }
  </SCRIPT>';
  
/**********
RESOURCES BEING MOVED
*************/
if(isset($_POST['add'])){

  setStart(3);
  
  //Get the fleet that is being added too
  if(isset($_POST['fleetid'])){
  
    $fid = (integer) escape_data($_POST['fleetid']);
    $res[0] = (integer) escape_data($_POST['rescivs']);
    $res[1] = (integer) escape_data($_POST['ressteel']);
    $res[2] = (integer) escape_data($_POST['rescylite']);
    $res[3] = (integer) escape_data($_POST['resplexi']);
    
    for($x=0; $x<4; $x++){
      if($res[$x]<0){
        echo 'Err';
        exit();
      }
    }
  
    //Make sure the fleet is on the planet and not moving
    if(getFleetStat(loc,$sid,$fid)!=$pid || getFleetStat(destination,$sid,$fid)!=0){
      echo 'error';
      exit();
    }
  
    //Make sure you own the planet and the fleet
    if(getFleetStat(ownerid,$sid,$fid)!=$id || getPlanetStat(ownerid,$sid,$pid)!=$id){
      echo 'er';
      exit();
    }
    
    $num = addCargo($res[0],$res[1],$res[2],$res[3],$sid,$pid,$fid);
    if($num > 0){
      echo 'You have added the cargo.<br><br>';
    }
    if($num == -1){
      echo 'There was not enough room on the fleet for this cargo.<br><br>';
    }
    if($num < -1){
      echo 'There are not enough resources on this planet.<br><br>';
    }

  }else{
    echo '<br>You must select the fleet that you want to add the resources too.<br><br>';
  }
}
/******
UNLOAD ALL
*******/
if(isset($_GET['u'])){

  setStart(3);
  
  $fid = escape_data($_GET['u']);
  
  //make sure you own
  if(getFleetStat(ownerid,$sid,$fid) != $id || $fid<1){
    echo 'err';
    exit();
  }
  
  //move all the resources off
  $arr = array(0=>'civs',1=>'steel',2=>'cylite',3=>'plexi');
  for($x=0; $x<sizeOf($arr); $x++){
    $str = $arr[$x];
    $num = getPlanetStat($str,$sid,$pid);
    $num += getFleetStat($str,$sid,$fid);
    setPlanetStat($str,$num,$sid,$pid);
    setFleetStat($str,0,$sid,$fid);
  }
  echo 'You have unloaded all the cargo.<br><br>';
}
/******
RESOURCES BEING ADDED TO PLANET
********/
if(isset($_POST['remove'])){
  if(isset($_POST['fleetid'])){
    
      setStart(3);
      
    $fid = escape_data($_POST['fleetid']);
    $res[0] = escape_data($_POST['rescivs']);
    $res[1] = escape_data($_POST['ressteel']);
    $res[2] = escape_data($_POST['rescylite']);
    $res[3] = escape_data($_POST['resplexi']);
    
    for($x=0; $x<4; $x++){
      if($res[$x]<0){
        echo 'Err';
        exit();
      }
    }
    
    //Make sure the fleet is on the planet and not moving
    if(getFleetStat(loc,$sid,$fid)!=$pid || getFleetStat(destination,$sid,$fid)!=0){
      echo 'error';
      exit();
    }
  
    //Make sure you own the planet and the fleet
    if(getFleetStat(ownerid,$sid,$fid)!=$id || getPlanetStat(ownerid,$sid,$pid)!=$id){
      echo 'er';
      exit();
    }
    
    $arr = array(0=>'civs',1=>'steel',2=>'cylite',3=>'plexi');
    $go = true;
    for($x=0; $x<sizeOf($arr); $x++){
      $num = getFleetStat($arr[$x],$sid,$fid);
      $num -= $res[$x];
      $res[$x] = $num;
      if($num<0){
        $go = false;
        break;
      }
    }
    
    if($go){
      for($x=0; $x<sizeOf($arr); $x++){
        setFleetStat($arr[$x],$res[$x],$sid,$fid);
      }
      echo 'You have removed the resources from that fleet.<br><br>';
    }else{  
      echo 'You do not have enough resources on that fleet.<br><br>';
    }
  
  }else{
    echo'<br>You must select the fleet that you want to remove the resources from.<br><br>';
  }
}
/********************
*SHIP BEING ADDED
*********************/
if(isset($_GET['s'])){

  setStart(4);
  
  $shipid = escape_data($_GET['s']);
  $buildable = true;
  
  $query = "SELECT id FROM mastership";
  $result = mysql_query($query);
  $num = mysql_num_rows($result);
  if($shipid<1 || $shipid>$num){
    echo 'err';
    exit();
  }
  
  //Check to see if we have the right fab plant level
  if(getPlanetStat(plant,$sid,$pid) < getShipStat(plantlevel,$shipid)){
    echo 'You do not have a high enough level fab plant to construct this.';
    exit();
  }
  //Check to see if we can build something
  if(shipQueueFull($sid,$pid,$id)){
    echo 'Your fabrication plant\'s queue is full.<br><br>';
  }else{
    $statsArray = array(0=>"civs", 1=>"steel", 2=>"cylite", 3=>"plexi");

    for($x = 0; $x<count($statsArray); $x++){
      
      //Get the cost
      $str = $statsArray[$x].'cost';
      
      //Fix the civs to civ bug
      if($x==0){
        $str = 'civcost';
      }
      
      //Check to see if you have enough resources
      if(getPlanetStat($statsArray[$x],$sid,$pid) <= getShipStat($str,$shipid)){
        if($x==1){
          echo 'You do not have enough elinarium.<br>';
        }else{
          echo 'You do not have enough '.$statsArray[$x].'<br>';
        }

        $buildable = false;
      }
    }
    echo '<br>';
    //Create the ship
    if($buildable){
    
      //Take the resources
      for($x=0; $x<count($statsArray); $x++){
        //Get the cost
        $str = $statsArray[$x].'cost';
        //Fix the civs to civ bug
        if($x==0){
          $str = 'civcost';
        }
        $current = getPlanetStat($statsArray[$x],$sid,$pid);
        $current = $current-getShipStat($str,$shipid);
        $query = "UPDATE planets$sid SET ".$statsArray[$x]."=$current WHERE id=$pid";
        $result = @mysql_query($query);
      }
      
      //Add the ship to queue
      //Last ship q on planets is equal to the finish time of the last created ship
      $lastship = getPlanetStat(lastshipq,$sid,$pid);
      if($lastship == 0){
        $lastship = time();
      }else{
    //if the last ship is already finished, set last ship = time
    if($lastship<time()){
      $lastship=time();
    }
    
    }
      
    
      
      $lastship = $lastship+calcShipBuildTime($shipid,$sid,$id);
      if(setPlanetStat(lastshipq,$lastship,$sid,$pid)){
        $query = "INSERT INTO shipq$sid(pid,ownerid,endtime,shiptype) VALUES($pid,$id,$lastship,$shipid)";
        $result = @mysql_query($query);
        if($result){
          echo 'You have added a '.getShipStat(name,$shipid).' to your queue.<br>';
        }else{
          echo 'There was an error, please contact support. ERRID#1';
        }
      }else{
        echo 'There was an error, please contact support. ERR2';
      }
    }
  }
}

/**********************
*SHIP COMPLETED
**********************/
if(isset($_GET['sf'])){

  setStart(4);
  
  $sf = escape_data($_GET['sf']);
  
  //Check to see if you own what is trying to be completed
  $query = "SELECT endtime,shiptype FROM shipq$sid WHERE ownerid=$id AND pid=$pid AND id=$sf";
  $result = mysql_query($query);
  $row2 = mysql_fetch_array($result);
  if($row2){
 
  $shiptype = $row2[1];
  
  //Check if time has passed
  if($row2[0]<time()){
    
    //Check if a fleet exists on the planet for the defend
    $query = "SELECT id FROM fleet$sid WHERE inport=1 AND loc=$pid AND ownerid=$id";
    $result = mysql_query($query);
    $row2 = mysql_fetch_array($result);
    if($row2){
      //Fleet exists
      $fid = $row2[0];
      
    }else{
      //No fleet exists, create
      $pname = getPlanetStat(name,$sid,$pid);
      $query = "INSERT INTO fleet$sid(loc,inport,ownerid,name) VALUES($pid,1,$id,'$pname')";
      $result = @mysql_query($query);
            echo mysql_error();
      $query = "SELECT id FROM fleet$sid WHERE inport=1 AND loc=$pid AND ownerid=$id";
      $result = mysql_query($query);
      $row2 = mysql_fetch_array($result);
      $fid = $row2[0];
    }
    
    //Add the ship to the fleet
    $str = "ship".$shiptype;
    $curnum = getFleetStat($str,$sid,$fid);
    $curnum++;
    if(setFleetStat($str,$curnum,$sid,$fid)){
      echo 'You have completed your ship. It is in your planets home fleet.<br>';
    }else{
      echo 'ERROR #1 contact sup';
    }
    
    //Delete the ship build record
    $query = "DELETE FROM shipq$sid WHERE id=$sf";
    $result = @mysql_query($query);
    if(!$result){
      echo 'Err 2';
    }
    
    //Check to see if there is another ship in the queue, if there isnt set the time to 0
    $query = "SELECT id FROM shipq$sid WHERE pid=$pid AND ownerid=$id";
    $result = mysql_query($query);
    $numrows = mysql_num_rows($result);
    if($numrows == 0){
      setPlanetStat(lastshipq,0,$sid,$pid);
    }
    
  }else{
    echo 'This has not finished building yet<br>';
  }
  }
}

/***********
*BUILDING COMPLETED
************/
if(isset($_GET['f'])){

  //Check if something is actually building
  $cid = getPlanetStat(constructionid,$sid,$pid);
    
  if($cid>0){
    
    //Check if it is finished
    if(timeLeft(getPlanetStat(constructiontime,$sid,$pid))<1){
    
      if(getBuildStat(increases,$cid) != "none"){
        $pstat = getPlanetStat(getBuildStat(increases,$cid),$sid,$pid);
        $increase = getBuildStat(increaseamt,$cid);
        $pstat = $pstat+$increase;
        
        if(setPlanetStat(getBuildStat(increases,$cid),$pstat,$sid,$pid)){
          if(setPlanetStat(constructionid,0,$sid,$pid)){
            echo getBuildStat(name,$cid).' has finished building.<br><br>';
          }else{
            echo 'There was an error trying to finish construction. If this persists please contact support. REF#1';
          }
        }else{
          echo 'There was an error trying to finish construction. If this persists please contact support. REF#2';
        }
      }else{
        if(setPlanetStat(constructionid,0,$sid,$pid)){
          if(!galaxyControlled($sid,$pid)){
            $z = getPlanetStat(z,$sid,$pid);
            $query = "UPDATE galaxy$sid SET ownerpid=$pid WHERE id=$z";
            $result = @mysql_query($query);
            echo getBuildStat(name,$cid).' has finished building.<br>You now have control over this system.<br>';
          }else{
            echo getBuildStat(name,$cid).' has finished building.<br>This action has failed. Someone has gained control of the system before you. You must destroy the enemy '.getBuildStat(name,$cid).' or conquer the planet.<br>';
          }
        }else{
          echo 'There was an error trying to finish construction. If this persists please contact support. REF#1';
        }
      }
    }
  }
}
      
/******************
*BUILDING STARTED
******************/
if(isset($_GET['bid'])){

  //Define a variable
  $buildable = true;
  
  //Check to see if the building is valid
  $query = "SELECT id FROM masterbuild";
  $result = mysql_query($query);
  $num = mysql_num_rows($result);
  $bid = escape_data($_GET['bid']);
  if($bid>$num || $bid<1){
    echo 'err, does not exist';
    exit();
  }
  //Check tosee if you are already in construction
  if(getPlanetStat(constructionid,$sid,$pid)>0){
    echo 'You are already building something.<br><br>';
  }else{
    //Define the array for easy looping
    $statsArray = array(0=>"civs", 1=>"steel", 2=>"cylite", 3=>"land", 4=>"plexi");
    
    //Check if you are building the special galaxy owner
    if(getBuildStat(increases,$bid) != 'none'){
    
      for($x = 0; $x<count($statsArray); $x++){
        //Check to see if you have enough resources for each element in array
        if(!canAfford($statsArray[$x],$bid,$sid,$pid)){
          if($x==1){
            echo 'You do not have enough elinarium.<br>';
          }else{
            echo 'You do not have enough '.$statsArray[$x].'<br>';
          }

          $buildable = false;
        }
      }
    }else{
      
      //Special case make sure you have enough money for special galaxy owner building
      for($x = 0; $x<count($statsArray); $x++){
        $str = $statsArray[$x].'cost';
        if(getPlanetStat($statsArray[$x],$sid,$pid) < getBuildStat($str,$bid)){
          $buildable=false;
        }
      }
    }

    //Create the building
    if($buildable){
      //Set the id, time then take away materials
      setPlanetStat(constructionid,$bid,$sid,$pid);
      
      if(getBuildStat(increases,$bid) != 'none'){
        setPlanetStat(constructiontime,calcBuildTime($bid,$sid,$pid)+time(),$sid,$pid);
      }else{
        setPlanetStat(constructiontime,getBuildStat(buildtime,$bid)+time(),$sid,$pid);
      }
      
      //Take the resources for special case
      if(getBuildStat(increases,$bid) == 'none'){
        for($x=0; $x<count($statsArray); $x++){
          $str = $statsArray[$x].'cost';
          $current = getPlanetStat($statsArray[$x],$sid,$pid);
          $current = $current-getBuildStat($str,$bid);
          $query = "UPDATE planets$sid SET ".$statsArray[$x]."=$current WHERE id=$pid";
          $result = @mysql_query($query);
        }
      }else{
        //Take the resources for default case
        for($x=0; $x<count($statsArray); $x++){
          $current = getPlanetStat($statsArray[$x],$sid,$pid);
          $current = $current-getCost($statsArray[$x],$bid,$sid,$pid);
          $query = "UPDATE planets$sid SET ".$statsArray[$x]."=$current WHERE id=$pid";
          $result = @mysql_query($query);
        }
      }
    }
  }
}

/************
PRINT PLANET DETAILS
**************/
echo '<u>Planet Details</u>
  <br>
  <br>
  <table cellspacing=5>
    <tr>
      <td></td>
      <td><b>Name</b></td>
      <td><b>Land</b></td>
      <td><b>Civs</b></td>
      <td><b>Elinarium</b></td>
      <td><b>Cylite</b></td>
      <td><b>Plexi</b></td>
      <td><b>Type</b></td>
    </tr><tr>
      <td></td>
      <td><a href="./name.php?id='.$pid.'">'.$row[name].'</a></td>
      <td>'.getPlanetStat(land,$sid,$pid).'/'.$row[landused].'</td>
      <td>'.getPlanetStat(civs,$sid,$pid).'/'.getPlanetStat(maxcivs,$sid,$pid).'</td>
      <td>'.getPlanetStat(steel,$sid,$pid).'</td>
      <td>'.getPlanetStat(cylite,$sid,$pid).'</td>
      <td>'.getPlanetStat(plexi,$sid,$pid).'</td>
      <td>'.getTypeStat(name,getPlanetStat(img,$sid,$pid)).'</td>
    </tr>
  </table>
  <br>';
/*****************
PLANET PRODUCTION
****************/
  echo '
  <br>
  <u>Planet Production</u> [Hourly]
  <br>
  <br>
  <table cellspacing=5>
    <tr>
      <td></td>
      <td><b>Civs</b></td>
      <td><b>Elinarium</b></td>
      <td><b>Cylite</b></td>
      <td><b>Plexi</b></td>
      <td><b>RP</b></td>
    </tr><tr>
      <td></td>
      <td>'.civProductionRate($sid,$pid).'</td>
      <td>'.resourceProductionRate(steel,$sid,$pid).'</td>
      <td>'.resourceProductionRate(cylite,$sid,$pid).'</td>
      <td>'.resourceProductionRate(plexi,$sid,$pid).'</td>
      <td>'.getPlanetStat(researchmod,$sid,$pid).'</td>
  </table>
  <br>';


/******
PRINT MENU
********/
echo '
<script language="Javascript">
var timer=0;

//Get Merchant text
function getText(){
  var ajaxRequest;
  try{
    // Opera 8.0+, Firefox, Safari
    ajaxRequest = new XMLHttpRequest();
  }catch (e){
    // Internet Explorer Browsers
    try{
      ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
    }catch (e) {
      try{
        ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
      }catch (e){
      // Browser does not support
      }
    }
  }
  ajaxRequest.onreadystatechange = function(){
    if(ajaxRequest.readyState == 4){
      var ajaxDisplay = document.getElementById("xhrPR");
      ajaxDisplay.innerHTML = ajaxRequest.responseText;
    }
  }
  var pid = document.getElementById("mpid").value;
  ajaxRequest.open("GET", "presources.php?pid="+pid+"&toid='.$pid.'", true);
  ajaxRequest.send(null); 
}

//Change the tab
var change = function(x){

   for(var y=1; y<6; y++){
    document.getElementById(y).style.display = "none";
  }
  if(document.getElementById(x).style.display == "block"){
    document.getElementById(x).style.display = "none";
  }else{
    document.getElementById(x).style.display = "block";
  }
}

//Buildcountdowns
var countdown = function(){
  if(timer<0){
    document.getElementById("btimeleft").innerHTML = "<a href=\"./build.php?id='.$pid.'&f=1\">Complete</a>";
  }else{
    document.getElementById("btimeleft").innerHTML = ""+timer;
    timer--;
    var timeout = setTimeout("countdown()",1000);
  }
}
var countStart = function(starttime){
  timer=starttime;
  countdown();
}

var shiptimer = 0;
var shipid=0;

var shipcountdown = function(){
  if(shiptimer<0){
    document.getElementById("sbuildtime").innerHTML = "<a href=\"build.php?&id='.$pid.'&sf="+shipid+"\">Complete</a>";
  }else{
    document.getElementById("sbuildtime").innerHTML = ""+shiptimer;
    shiptimer--;
    var timeout = setTimeout("shipcountdown()",1000);
  }
}
var shipStart = function(starttime,theshipid){
  shiptimer=starttime;
  shipid=theshipid;
  shipcountdown();
}

</script>

<center>
<br>
<a href="javascript:;" onClick="change(3)">Fleet Resources</a> | 
<a href="javascript:;" onClick="change(1)">Move Fleets</a> | 
<a href="javascript:;" onClick="change(5)">Merchant Port</a> | 
<a href="javascript:;" onClick="change(4)">Fabrication Plant</a> | 
<a href="javascript:;" onClick="change(2)">Construction</a>
</center>';
/****************
PRINT MOVE FLEETS
*************/
echo '<div id="1" style="display:none">
  <hr>
  <form name="fleetdd" action="./planetdetails.php?id='.$pid.'" method="post">
  <br>
  <u>Move Fleets</u>
  <br>';
//Populate dropdown only if a fleet exists
$query = "SELECT name,id,loc FROM fleet$sid WHERE ownerid=$id AND destination=0 AND loc!=$pid";
$result = mysql_query($query);
$num = mysql_num_rows($result);
if($num>0){
$first=true;
  while($row = mysql_fetch_array($result)){
    $traveltime = travelTime($sid,$row[loc],$pid,$id);
    if($traveltime>0){
      if($first){
        $first=false;
        echo '<br>Select a fleet to move to this planet.<br><br><table cellspacing=5>
            <tr>
            <td></td>
            <td>Fleet</td>
          <td><select name="fleet">';
      }
        echo '<option value="'.$row[id].'">'.$row[name].' ['.fleetSize($sid,$row[id]).'] '.(floor($traveltime-time())).'</option>';
    }
  }
  if(!$first){
  echo '<td><input type="submit" value="Send Fleet"></td>
    </tr>
    </table>';
  }else{
    echo '<br>You have no fleets that can reach this planet.';
  }
}else{
  echo '<br>
      <table cellspacing=5>
        <tr>
          <td></td>
          <td>You do not control any fleets located on any other planet.</td>
        </tr>
      </table>';
}

echo '<input type="hidden" value="true" name="hide"></form></div>';
/**************
PRINT FLEET RESOURCES
***************/
echo '<div id="3" style="display:none"><hr>
  <br>
  <u>Fleet Resources</u>
  <br>
  <form name="fleetcargo" action="./build.php?id='.$pid.'" method="post">
  <br>';
    
$query = "SELECT id,name,civs,steel,cylite,plexi FROM fleet$sid WHERE ownerid=$id AND loc=$pid AND destination=0";
$result = mysql_query($query);
$num = mysql_num_rows($result);
if($num>0){
  echo '
  <table cellspacing="5" cellpadding="5">
    <tr>
      <td></td>
      <td></td>
      <td>Name</td>
      <td>Cargo</td>
      <td>Civs</td>
      <td>Elin.</td>
      <td>Cylite</td>
      <td>Plexi</td>
      <td></td>
    </tr>';
}
while($row = mysql_fetch_array($result)){
  echo '<tr>
      <td></td>
      <td><input type="Radio" name="fleetid" value="'.$row[id].'"></td>
      <td>'.$row[name].'</td>
      <td>'.currentCargo($sid,$row[id]).'/'.maxCargo($sid,$row[id]).'</td>
      <td>'.$row[civs].'</td>
      <td>'.$row[steel].'</td>
      <td>'.$row[cylite].'</td>
      <td>'.$row[plexi].'</td>
      <td><a href="./build.php?id='.$pid.'&u='.$row[id].'">Unload All</a></td>
    </tr>';
}
if($num>0){
  echo '
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><input type="text" name="rescivs" size=1 value=0></td>
      <td><input type="text" name="ressteel" size=1 value=0></td>
      <td><input type="text" name="rescylite" size=1 value=0></td>
      <td><input type="text" name="resplexi" size=1 value=0></td>
    </tr><tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><input type="submit" name="add" value="Pick Up"></td>
      <td><input type="submit" name="remove" value="Drop Off"></td>
    </tr>
      </form></table>';
}else{
  echo '<table cellspacing=5>
      <tr>
        <td></td>
        <td>You do not have any fleets located on this planet.</td>
      </tr>
      </table>';
}
echo '</div>';
/****************
PRINT MERCHANT PORT DETAILS
*****************/
echo '<div id="5" style="display:none"><hr>
  <br>
  <u>Merchant Port</u> [Level '.getPlanetStat(merchant,$sid,$pid).']
  <br>
  <br>';
  
  //Check if they have a merchant port
  if(getPlanetStat(merchant,$sid,$pid)==0){
    echo 'You do not have a merchant port.';
  }else{
    //Display the planets they can get
    //Populate dropdown only if a fleet exists
    $query = "SELECT name,id FROM planets$sid WHERE ownerid=$id AND id!=$pid";
    $result = mysql_query($query);
    $num = mysql_num_rows($result);
    if($num>0){
      $first=true;
      while($row = mysql_fetch_array($result)){
        $traveltime = travelTime($sid,$row[id],$pid,$id);
        if($traveltime>0){
          if($first){
            $first=false;
            echo '<br><u><b>Hire Merchants</b></u><br><br>
        <table cellspacing=5>
        <td></td>
              <td><select name="mpid" id="mpid" onChange="getText()">
          <option>Select Planet</option>';
          }
          echo '<option value="'.$row[id].'">'.$row[name].' ['.(floor($traveltime-time())).']</option>';
        }
      }
      if(!$first){
    //Print the resources avail on the planet
    echo'</tr></table><span id="xhrPR">
      <br>Select a planet to move resources from.</span>';
      }else{
        echo '<br>You do not have any planets that can reach this planet.';
      }
    }
  }
  //List all merchants on the way
  $first=true;
  echo '<br><br><u><b>Hired Merchants</b></u><br><br>';
  $query = "SELECT id,civs,steel,cylite,plexi,arrivaltime FROM merchants$sid WHERE ownerid=$id AND did=$pid";
  $result = mysql_query($query);
  while($row = mysql_fetch_array($result)){
  //See if this merchant has landed
  if(timeLeft($row[arrivaltime])<1){
    $thetime="Unloading Resources";
    $newciv = getPlanetStat(civs,$sid,$pid)+$row[civs];
    $newsteel = getPlanetStat(steel,$sid,$pid)+$row[steel];
    $newcylite = getPlanetStat(cylite,$sid,$pid)+$row[cylite];
    $newplexi = getPlanetStat(plexi,$sid,$pid)+$row[plexi];
    $query2 = "UPDATE planets$sid SET civs=$newciv,steel=$newsteel,cylite=$newcylite,plexi=$newplexi WHERE id=$pid";
    $result2 = @mysql_query($query2);
    $query2 = "DELETE FROM merchants$sid WHERE id=$row[id]";
    $result2 = @mysql_query($query2);
  }else{
    $thetime=timeLeft($row[arrivaltime]);
  }
  if($first){
    echo'<table cellspacing=5>
        <tr>
          <td><b>Civs</b></td>
          <td><b>Elin</b></td>
          <td><b>Clyite</b></td>
          <td><b>Plexi</b></td>
          <td><b>Arrival Time</b></td>
        </tr>';
    $first=false;
  }
  echo '<tr>
      <td>'.$row[civs].'</td>
      <td>'.$row[steel].'</td>
      <td>'.$row[cylite].'</td>
      <td>'.$row[plexi].'</td>
      <td>'.$thetime.'</td></tr>';
  }
if($first){
  echo 'You have no hired merchants.';
}else{
  echo '</table>';
}
  echo '</div>';
/**************
PRINT FAB PLANT DETAILS
*************/
echo'<div id="4" style="display:none;"><hr>
  <br>
  <u>Fabrication Plant</u> [Level '.getPlanetStat(plant,$sid,$pid).']
  <br>
  <br>
  <table cellspacing=5>
    <tr>';
  
  //Check to see if a ship is being built
  
  if(getPlanetStat(plant,$sid,$pid)>0){
    if(getPlanetStat(lastshipq,$sid,$pid)>0){
      $query = "SELECT id,endtime,shiptype FROM shipq$sid WHERE pid=$pid AND ownerid=$id ORDER BY endtime ASC";
      $result = mysql_query($query);
      $first = true;
      $count = 1;
      while($row2 = mysql_fetch_array($result, MYSQL_ASSOC)){
        echo '<td></td>
            <td>'.$count.'. '.getShipStat(name,$row2[shiptype]).'</td>';
        if($first){
          $first=false;
          if(timeLeft($row2[endtime])>0){
            echo '<td><span id="sbuildtime">'.timeLeft($row2[endtime]).'</span></div></td>';
          echo '<script language="JavaScript">
          shipStart('.timeLeft($row2[endtime]).','.$row2[id].');
          </script>';
          }else{
            echo '<td><a href="build.php?&id='.$pid.'&sf='.$row2[id].'">Complete</a></td>';
          }
        }else{
          echo '<td>Queued</td>';
        }
        echo '</tr><tr>';
        $count++;
      }
    }else{
      echo '<td>You do not have any ships in queue</td>';
    }
    echo '</tr></table><table cellspacing=5>';
    for($tree=0;$tree<4;$tree++){
    echo '<tr></tr><tr></tr><tr></tr><tr></tr>';
      if($tree==0){
        echo '<tr><td></td><td><b><u>Offensive Ships</u></b></td></tr>';
      }else if($tree==1){
        echo '<tr></tr><tr></tr><tr><td></td><td><b><u>Defensive Ships</u></b></td></tr>';
      }else if($tree==2){
        echo '<tr></tr><tr></tr><tr><td></td><td><b><u>Tank Ships</u></b></td></tr>';
      }else{
        echo '<tr></tr><tr></tr><tr><td></td><td><b><u>Utility Ships</u></b></td></tr>';
      }
      //Print all buildable ships
    echo '
        <tr><td></td>
          <td><b>Name</b></td>
          <td><b>HP</b></td>
          <td><b>Attack</b></td>
          <td><b>Def</b></td>
          <td><b>Civs</b></td>
          <td><b>Elinarium</b></td>
          <td><b>Cylite</b></td>
          <td><b>Plexi</b></td>
          <td><b>Time</b></td></tr>';
    $query = "SELECT id,plantlevel FROM mastership WHERE tree=$tree ORDER BY ordering ASC";
    $result = mysql_query($query);
    while($row = mysql_fetch_array($result)){
      if(getPlanetStat(plant,$sid,$pid) >= $row[plantlevel]){
        $x=$row[id];
        echo'<tr><td></td>
            <td><a href="./pedia.php?id='.$x.'&type=2">'.getShipStat(name,$x).'</td>
            <td>'.getShipStat(hp,$x).'</td>
            <td>'.getShipStat(attack,$x).'</td>
            <td>'.getShipStat(defend,$x).'</td>
            <td>'.getShipStat(civcost,$x).'</td>
            <td>'.getShipStat(steelcost,$x).'</td>
            <td>'.getShipStat(cylitecost,$x).'</td>
            <td>'.getShipStat(plexicost,$x).'</td>
            <td>'.calcShipBuildTime($x,$sid,$id).'</td>
            <td><a href="./build.php?id='.$pid.'&s='.$x.'">Build</a></td>
          </tr>';
      }
    }
    }
  }else{
    echo '<td>You do not have a Fabrication Plant</td>';
  }
echo'</table>
  <br>
  </div>';
  
/*************
PRINT CONSTRUCTION DETAILS
************/
echo '<div id="2" style="display:block;"><hr>
  <br>
  <u>Current Construction</u>
  <br>
  <br>
  <table cellspacing=5>
    <tr>
      <td></td>
      <td>';
    //Check if something is being built
  if(getPlanetStat(constructionid,$sid,$pid)>0){
    echo getBuildStat(name,getPlanetStat(constructionid,$sid,$pid));
    //Check if building is finished
    if(timeLeft(getPlanetStat(constructiontime,$sid,$pid))>0){
      echo ' finishing in <span id="btimeleft">'.timeLeft(getPlanetStat(constructiontime,$sid,$pid)).'</span> seconds. <a href="javascript:confirmPost();">Cancel</a></td>';
    echo '<script language="JavaScript">
        countStart('.timeLeft(getPlanetStat(constructiontime,$sid,$pid)).');
      </script>';
    }else{
      echo ' is being finalized. <a href="./build.php?id='.$pid.'&f=1">Complete</a></td>';
    }
  }else{//Nothing is being built
    echo 'Nothing in construction</td><td></td>';
  }
  echo '<table cellspacing=5>';
 for($tree=0;$tree<3;$tree++){
    echo '<tr></tr><tr></tr><tr></tr><tr></tr>';
      if($tree==0){
        echo '<tr><td></td><td><b><u>Economy</u></b>';
      }else if($tree==1){
        echo '<tr></tr><tr></tr><tr><td></td><td><b><u>Utility</u></b>';
      }else{
        echo '<tr></tr><tr></tr><tr><td></td><td><b><u>Defense</u></b>';
      }
echo'</tr>
    <tr>
      <td></td>
      <td><b>Name</b></td>
      <td><b>Level</b></td>
      <td></td>
      <td><b>Land</b></td>
      <td><b>Civs</b></td>
      <td><b>Elinarium</b></td>
      <td><b>Cylite</b></td>
      <td><b>Plexi</b></td>
      <td><b>Time</b></td>';
      
$query = "SELECT id FROM masterbuild WHERE tree=$tree";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
  $x = $row[id];
  $str = getBuildStat(increases,$x);
  
  if($str != "none"){
    $amt = getPlanetStat($str,$sid,$pid);
    if($str == "maxcivs"){
      $amt/=50;
    }
  
    echo '<tr><td></td>
        <td><a href="./pedia.php?id='.$x.'">'.getBuildStat(name,$x).'</a></td>
        <td>'.$amt.'</td>
        <td><a href="javascript:confirmBuilding('.$x.');">X</a></td>
        <td>'.getBuildStat(landcost,$x,$sid,$pid).'</td>
        <td>'.getCost(civs,$x,$sid,$pid).'</td>
        <td>'.getCost(steel,$x,$sid,$pid).'</td>
        <td>'.getCost(cylite,$x,$sid,$pid).'</td>
        <td>'.getCost(plexi,$x,$sid,$pid).'</td>
        <td>'.calcBuildTime($x,$sid,$pid).'</td>
        <td><a href="./build.php?id='.$pid.'&bid='.$x.'">Build</a></td>
      </tr>';
  }else{
    if(!galaxyControlled($sid,$pid)){
      echo '<tr><td></td>
          <td><a href="./pedia.php?id='.$x.'">'.getBuildStat(name,$x).'</a></td>
          <td>-</td>
          <td>-</td>
          <td>'.getBuildStat(landcost,$x,$sid,$pid).'</td>
          <td>'.getBuildStat(civscost,$x).'</td>
          <td>'.getBuildStat(steelcost,$x).'</td>
          <td>'.getBuildStat(cylitecost,$x).'</td>
          <td>'.getBuildStat(plexicost,$x).'</td>
          <td>'.getBuildStat(buildtime,$x).'</td>
          <td><a href="./build.php?id='.$pid.'&bid='.$x.'">Build</a></td>
        </td>';
    }
  }
}}
echo '</table></div>';

//Include footer
echo '<SCRIPT LANGUAGE="JavaScript">change(changer);</SCRIPT>';
include('./footer.php');
?>