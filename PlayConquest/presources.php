<?php /**/ ?><?php

include('./db_connect.php');

session_start();

if(isset($_GET[pid])){
  $pid = (integer) escape_data($_GET[pid]);
  if($pid==0){
    echo 'Select a planet.';
    exit();
  }
}else{
  echo 'Invalid Planet';
}
if(isset($_GET[toid])){
  $toid = (integer) escape_data($_GET[toid]);
  if($toid==0){
    echo 'Select a planet.';
    exit();
  }
}else{
  echo 'Invalid Planet';
}

$userid = $_SESSION['user_id'];
$sid = $_SESSION['sid'];

//Check if you own
if($userid!=getPlanetStat(ownerid,$sid,$pid)){
  echo 'Invalid Planet';
  exit();
}
if($userid!=getPlanetStat(ownerid,$sid,$toid)){
  echo 'err';
  exit();
}

echo '<form name="merchant" action="./build.php?id='.$toid.'&mid='.$pid.'" method="post"><table cellspacing=10>
        <tr>
        <td></td>
        <td></td>
        <td><b>Civs</b></td>
        <td><b>Elin</b></td>
        <td><b>Cylite</b></td>
        <td><b>Plexi</b></td>
    </tr><tr>
        <td></td>
        <td><b>'.getPlanetStat(name,$sid,$pid).'</td>
        <td>'.getPlanetStat(civs,$sid,$pid).'</td>
        <td>'.getPlanetStat(steel,$sid,$pid).'</td>
        <td>'.getPlanetStat(cylite,$sid,$pid).'</td>
        <td>'.getPlanetStat(plexi,$sid,$pid).'</td>
    </tr><tr>
        <td></td>
        <td>Move</td>
        <td><input type="text" name="mcivs" size=1 value=0></td>
        <td><input type="text" name="msteel" size=1 value=0></td>
        <td><input type="text" name="mcylite" size=1 value=0></td>
        <td><input type="text" name="mplexi" size=1 value=0></td>
    </tr><tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <tD><input type="submit" name="merchant" value="Hire Merchant"></td></table></form>';
?>