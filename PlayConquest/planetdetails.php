<?php /**/ ?><?php

include('./header.php');
  
//Make sure they are on a planet
if(isset($_GET['id'])){
  $pid = (integer) escape_data($_GET['id']);
}else{
  exit();
}

/*******
SENDING FLEET ATTACK
*******/
if(isset($_POST[hide])){
  
  //Get fleet id
  $fid = (integer) escape_data($_POST[fleet]);
  
  //If they dont own the planet, check if it is under protection
  if(getPlanetStat(starttime,$sid,$pid)+259200>time() && getPlanetStat(ownerid,$sid,$pid)!=$id){
    echo 'This player is under protection.';
    exit();
  }
  
  //Check that they own the fleet
  if(getFleetStat(ownerid,$sid,$fid) == $id){
    //check that the fleet is not moving
    if(getFleetStat(destination,$sid,$fid)>0){
      echo 'This fleet is already moving.';
    }else{
      $move = moveFleet($fid,$sid,getFleetStat(loc,$sid,$fid),$pid);
      if($move>0){
        $goer = 1;
        echo 'You have sent your fleet.<br>';
      }
      if($move == -1){
        $goer = 2;
        echo 'You can not move an empty fleet.<br>';
      }
      if($move == -2){
        $goer = 3;
        echo 'It is impossible for this fleet to reach that System.<br>';
      }
    }
  }else{
    echo 'err';
    exit();
  }
}
/**********
SENDING PROBES
**********/
if(isset($_POST['thesubmit'])){

  $fid = (integer) escape_data($_POST[spy]);

  //Check that they own the fleet
  if(getFleetStat(ownerid,$sid,$fid) != $id){
    echo 'err';
    exit();
  }
  
  //Check that the fleet has a probe value
  if(!(getSpyNum($sid,$fid)>0)){
    echo 'err2';
    exit();
  }
  
  //Check if it is already probing
  if(getFleetStat(probes,$sid,$fid)>0){
    echo 'This fleets probes are already launched.';
  }else{
    setFleetStat(probes,$pid,$sid,$fid);
    $traveltime = travelTime($sid,getFleetStat(loc,$sid,$fid),$pid,$id);
    setFleetStat(probetime,$traveltime,$sid,$fid);
    echo 'You have sent your probes.<br>';
  }
}

//See if the person owns the planet
if(getPlanetStat(ownerid,$sid,$pid) == $id){
  if($goer){
    changePage('./build.php?id='.$pid.'&err='.$goer);
  }else{
    changePage('./build.php?id='.$pid);
  }
  include('./footer.php');
  exit();
}


/************
PRINT PLANET DETAILS HEADER
**************/
//Get owner name
$ownerid = getPlanetStat(ownerid,$sid,$pid);
if($ownerid>0){
  $name = getUserStat(username,$ownerid);
}else{
  $name = 'None';
}

$z = getPlanetStat(z,$sid,$pid);
$query = "SELECT name FROM galaxy$sid WHERE id=$z";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$gname = $row[0];

echo '
  <table cellspacing=5 align="center">
    <tr>
      <td></td>
    <td><b>Galaxy</b></td>
      <td><b>Name</b></td>
      <td><b>Owner</b></td>
      <td><b>Land</b></td>
      <td><b>Type</b></td>
    </tr><tr>
      <td></td>
    <td><a href="./map.php?gid='.$z.'">'.$gname.'</a></td>
      <td>'.getPlanetStat(name,$sid,$pid).'</td>
      <td>'.$name.'</td>
      <td>'.getPlanetStat(landused,$sid,$pid).'</td>
      <td>'.getTypeStat(name,getPlanetStat(img,$sid,$pid)).'</td>
    </tr>
  </table>';
  
/*********
PRINT JAVASCRIPT TABS
***********/
echo '
<script language="Javascript">
var change = function(x){

   for(var y=0; y<2; y++){
    document.getElementById(y).style.display = "none";
  }
  if(document.getElementById(x).style.display == "block"){
    document.getElementById(x).style.display = "none";
  }else{
    document.getElementById(x).style.display = "block";
  }
}
</script>

<center>
<br>
<a href="javascript:;" onClick="change(0)">Attack</a> | 
<a href="javascript:;" onClick="change(1)">Probe</a><br><br></center>
';
/*******
PRINT ATTACK MENU
***********/
  echo'<div id="0" style="display:block">';

//Populate dropdown
$first=true;
$protection=false;
$query = "SELECT name,id,loc FROM fleet$sid WHERE ownerid=$id AND destination=0 AND loc!=$pid";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
  $traveltime = travelTime($sid,$row[loc],$pid,$id);
 if(getPlanetStat(starttime,$sid,$pid)+259200<time()){
  if($traveltime>0){
    if($first){
        echo '<form name="fleetdd" action="./planetdetails.php?id='.$pid.'" method="post">
            <table cellspacing=5 align="center">
              <tr>
                <td></td>
                <td><select name="fleet">';
        $first=false;
    }
    echo '<option value="'.$row[id].'">'.$row[name].' ['.fleetSize($sid,$row[id]).'] '.($traveltime-time()).'</option>';
  }
 }else{
  
  echo 'This player is still under protection from the Fair Space Colonization Act, this player has recently started and can not be attacked for 3 days after the start of his colonization.';
  $first=false;
  $protection=true;
  break;
 }
}
if(!$first){
  if(!$protection){
    echo '</td><td><input type="submit" value="Send Fleet"></td></tr></table><input type="hidden" value="true" name="hide"></form>';
  }
}else{
  echo 'You have no fleets capable of reaching this planet for an attack. Someone might control a system between your fleets and this one.';
}
echo '</div>';

/***********
PRINT SPY
************/
echo '<div id="1" style="display:none">';
//Populate dropdown for spies
$first = true;
$query = "SELECT id,name,loc FROM fleet$sid WHERE ownerid=$id AND destination=0 AND loc!=$pid AND probes=0";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
  $num = getSpyNum($sid,$row[id]);
  $traveltime = travelTime($sid,$row[loc],$pid,$id);
  if($num>0 && $traveltime>0){
    if($first){
      $first=false;
      echo '<form name="fleetdd" action="./planetdetails.php?id='.$pid.'" method="post">
      <table cellspacing=5 align=center>
        <tr>
          <td><select name="spy">';
    }
    echo '<option value="'.$row[id].'">'.$row[name].' ['.$num.'] '.($traveltime-time()).'</option>';
  }
}

if(!$first){
  echo '</td><td><input type="submit" name="thesubmit" value="Send Probes"></td></td></table></form>';
}else{
  echo 'You have no fleets capable of sending a probe to this planet.';
}
echo '</div>';
    
include('./footer.php');
?>  