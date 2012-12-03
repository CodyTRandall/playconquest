<?php

include('./header.php');

function hasResources($sid,$fid){
  if( (getFleetStat(civs,$sid,$fid) != 0) || (getFleetStat(steel,$sid,$fid) !=0) || (getFleetStat(cylite,$sid,$fid) !=0) || getFleetStat(plexi,$sid,$fid) !=0){
    return true;
  }
  return false;
}
//Establish variables and check if errors
if(isset($_GET['id'])){
  $fid = (integer) escape_data($_GET['id']);
  
  $query = "SELECT id FROM fleet$sid WHERE id=$fid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  if(!$row){
    echo 'err';
    exit();
  }
}else{
  echo 'Error';
  exit();
}

if(!isset($id)){
  echo 'Error';
  exit();
}

/************
COMBINE FLEETS
*************/
if(isset($_POST['fleetadd'])){

  $fleetSelected = (integer) escape_data($_POST['fleet']);
  
  //make sure the fleets are on the same planet and not moving
  if(getFleetStat(ownerid,$sid,$fid) != getFleetStat(ownerid,$sid,$fleetSelected)){
    echo 'eerr';
    exit();
  }
  if(getFleetStat(destination,$sid,$fid)!=0 || getFleetStat(destination,$sid,$fid)!=0){
    echo 'err2';
    exit();
  }
  
  //Make sure that the current fleet has no resources
  if(hasResources($sid,$fid)){
    echo 'You can not move ships that still have resources';
    exit();
  }
  $query = "SELECT id FROM mastership";
  $result = @mysql_query($query);
  $numship = mysql_num_rows($result);
  
  //get all of the ship numbers to move
  for($x=1; $x<$numship+1; $x++){
    $str = 'ship'.$x;
    if(isset($_POST[$str])){
      $ship[$x] = $_POST[$str];
    }else{
      $ship[$x] = 0;
    }
    $ship[$x] = max(0,$ship[$x]);
  }
  
  $go = true;
  //Make sure it has enough of each ship
  for($x=1; $x<sizeOf($ship)+1; $x++){
    $str = 'ship'.$x;
    $num = getFleetStat($str,$sid,$fid);
    if($num<$ship[$x]){
      echo 'You do not have enough ships to move.';
      $go = false;
    }
  }
  
  if($go){
    //move the ships
    for($x=1; $x<sizeOf($ship)+1; $x++){
      $str = 'ship'.$x;
      
      //Add the ships
      $num = getFleetStat($str,$sid,$fleetSelected);
      $num += $ship[$x];
      setFleetStat($str,$num,$sid,$fleetSelected);
      
      //Take away ships
      $num = getFleetStat($str,$sid,$fid);
      $num -= $ship[$x];
      setFleetStat($str,$num,$sid,$fid);
      
    }
  
  echo 'You have moved the ships successfully<br>';
  }
  
}
      
if(isset($_GET['fa'])){
  
  $fleetSelected = $_POST['fleet'];
  $counter;
  $str;
  for($z=0;$z<count($fleetSelected);$z++){
  
    $fleetSelected[$z] = escape_data($fleetSelected[$z]);  
    for($i=1; $i<4; $i++){
      $str = "ship".$i;
      $counter[$i] += getFleetStat($str,$sid,$fleetSelected[$z]);
    }
    $thisPid = getFleetStat(loc,$sid,$fleetSelected[$z]);
    $ownerId = getFleetStat(ownerid,$sid,$fleetSelected[$z]);
    
    if($id!=$ownerId){
      echo 'err1';
      exit();
    }
    if($thisPid!=$pid){
      echo 'err';
      exit();
    }

  }
  
  //add then destroy
  for($i=1; $i<4; $i++){
    $str = "ship".$i;
    setFleetStat($str,$counter[$i],$sid,$fleetSelected[0]);
  }

  for($z=1; $z<count($fleetSelected); $z++){
    $query = "DELETE FROM fleet$sid WHERE id=$fleetSelected[$z]";
    $result = @mysql_query($query);
  }
}


    
/********
PRINT THE FLEET DETAILS
*********/
echo '<table cellspacing=5 align=center>
    <tr>
      <td><b>Name</b></td>
      <td><b>Loc</b></td>
      <td><b>Destination</b></td>
      <td><b>Arrival Time</b></td>
    </tr><tr>
      <td>'.getFleetStat(name,$sid,$fid).'</td>
      <td><a href="./planetdetails.php?id='.getFleetStat(loc,$sid,$fid).'">'.getPlanetStat(name,$sid,getFleetStat(loc,$sid,$fid)).'</a></td>';

$destination = getFleetStat(destination,$sid,$fid);
if($destination > 0){
  echo'<td><a href="./planetdetails.php?id='.getFleetStat(loc,$sid,$fid).'">'.getPlanetStat(name,$sid,getFleetStat(destination,$sid,$fid)).'</a></td>
      <td>'.timeLeft(getFleetStat(destinationtime,$sid,$fid)).'</td>';
}else{
  echo'<td>None</td>
      <td>---</td>';
}
echo '</tr></table><br><hr><br>';

//Print the list of ships
echo '<form name="fleetadd" action="./fleetdetails.php?id='.$fid.'" method="post">
  <table cellspacing=5 align=center>
    <tr>
      <td></td>
      <td><b>Type</b></td>
      <td><b>Attack</b></td>
      <td><b>Defence</b></td>
      <td><b>Amt</b></td>
      <td><b>Move</b></td>
      <td></td>
    </tr>';

$query = "SELECT id FROM mastership";
$result = mysql_query($query);
$num = mysql_num_rows($result);
for($x = 1;$x<$num+1; $x++){
  $str = "ship".$x;
  if(getFleetStat($str,$sid,$fid)>0){
  echo '<tr>
      <td></td>
      <td>'.getShipStat(name,$x).'</td>
      <td>'.getShipStat(attack,$x).'</td>
      <td>'.getShipStat(defend,$x).'</td>
      <td>'.getFleetStat($str,$sid,$fid).'</td>
      <td><input type="text" name="ship'.$x.'" value="0" size="1"></td>
      <td></td>
    </tr>';
  }
}

echo '</table>';
/***************
PRINT OTHER FLEETS
***************/
echo '<center>';
if(getFleetStat(destination,$sid,$fid)==0){
$pid = getFleetStat(loc,$sid,$fid);
$query = "SELECT id,name,ship1,ship2,ship3,ship4,ship5,ship6,ship7 FROM fleet$sid WHERE ownerid=$id AND loc=$pid AND destination=0";
$result = mysql_query($query);
if(hasResources($sid,$fid)){
  echo '<br><br>You can not move ships in a fleet that still has resources.';
}else{
if(mysql_num_rows($result)==1){
  echo '<br><br>You have no other fleets at this location';
}else{
  echo '<select name="fleet">';
  while($row = mysql_fetch_array($result)){
    if($row[id] != $fid){
      echo '<option value="'.$row[id].'">'.$row[name].' ['.($row[ship1]+$row[ship2]+$row[ship3]+$row[ship4]+$row[ship5]+$row[ship6]+$row[ship7]).']</option>';
    }
  }
  echo '
    <input type="submit" value="Move Ships">
    <input type="hidden" value="true" name="fleetadd">
    </form>';  
}
}
}
echo '</center>';
include('./footer.php');

?>


  
