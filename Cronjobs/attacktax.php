<?php

//include('./db_connect.php');
require_once('./www.playconquest.com/db_connect.php');

function getShipNumber(){
  $query = "SELECT id FROM mastership";
  $result = @mysql_query($query);
  return mysql_num_rows($result);
}

function destroyFleet($sid,$fid){
  $query = "DELETE FROM fleet$sid WHERE id=$fid";
  $result = @mysql_query($query);
  return true;
}

function calchp($sid,$fid){
  for($x=1; $x<getShipNumber()+1; $x++){
    $str = 'ship'.$x;
    $query = "SELECT $str FROM fleet$sid WHERE id=$fid";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    $hp += $row[0]*getShipStat(hp,$x);
  }
  return $hp;
}
  
$servercount = getServerNumber();
for($sid=1; $sid<$servercount+1; $sid++){
  $query = "SELECT id FROM fleet$sid WHERE combatid>0";
  $result = @mysql_query($query);

  while($row = mysql_fetch_array($result)){

    //Get the attacking fleet id
    $afid = $row[id];
    
    //Make sure time has elapsed and the person doesnt own the planet already
    $dpid = getFleetStat(destination,$sid,$afid);
    
    if(timeLeft(getFleetStat(destinationtime,$sid,$afid))<1 && getFleetStat(ownerid,$sid,$afid)!=getPlanetStat(ownerid,$sid,$dpid)){
    
    
    //Create a defending fleet with all fleets
    $first = true;
    $query = "SELECT id FROM fleet$sid WHERE loc=$dpid AND destination=0";
    $result = mysql_query($query);
    while($row = mysql_fetch_array($result)){
      if($first){
        $dfid = $row[id];
        $first = false;
      }else{
        for($i=1; $i<getShipNumber()+1; $i++){
          $str = "ship".$i;
          $thisship = getFleetStat($str,$sid,$dfid);
          $thisship += getFleetStat($str,$sid,$row[id]);
          setFleetStat($str,$thisship,$sid,$dfid);
          setFleetStat($str,0,$sid,$row[id]);
        }  
      }
    }
    ///////////////////////////////////////////////////////////

    $str = "ship";
    $endstr;
    //Get variables
    for($x=1; $x<getShipNumber()+1; $x++){
      $str = "ship".$x;
      $ashiparray[$x] = getFleetStat($str,$sid,$afid);
      $attack += getShipStat(attack,$x)*$ashiparray[$x];
    }
    for($x=1; $x<getShipNumber()+1; $x++){
      $str = "ship".$x;
      $dshiparray[$x] = getFleetStat($str,$sid,$dfid);
      $defence += getShipStat(defend,$x)*$dshiparray[$x];
    }
  
  //Add the defense numbers
  $defence += getPlanetStat(cannon,$sid,$dpid);

    //Make attack between 90-110%
    $attack = $attack*rand(90,110)/100;

    //Make defence between 95-115%
    $defence = $defence*rand(95,115)/100;
    
    //Add research
    $aplayerid = getFleetStat(ownerid,$sid,$afid);
    $dplayerid = getFleetStat(ownerid,$sid,$dfid);
    
    $attack *= getResearch(2,$sid,getFleetStat(ownerid,$sid,$afid));
    $defence *= getResearch(6,$sid,getFleetStat(ownerid,$sid,$dfid));

    setFleetStat(combatid,0,$sid,$afid);
  
/************************
Do destruction of ships,
Take attack, subtract hp, winners destroy all losers
*************************/
if($attack>$defence){
  //Destroy losers fleet
  destroyFleet($sid,$dfid);

  $endstr1 = 'You conquered <a href="./planetdetails.php?id='.$dpid.'">'.getPlanetStat(name,$sid,$dpid).'</a> with the fleet '.getFleetStat(name,$sid,$afid).' Your attack won with '.$attack.' attack to '.$defence.' defence.';
  $endstr2 = 'Your planet <a href="./planetdetails.php?id='.$dpid.'">'.getPlanetStat(name,$sid,$dpid).'</a> was conquered by <a href="./profile.php?id='.$aplayerid.'">'.getUserStat(username,$aplayerid).'</a> with '.$attack.' attack to '.$defence.' defense.';
  $tempdef = $defence;
  echo $endstr1;
  //For total dmg, do dmg to an attacking ship, if the saved dmg is enough to destroy the ship,
  //subtract it from the shiparraycounter then at the end update.

  $dahp = calchp($sid,$afid);
  if($dahp<$tempdef){
    destroyFleet($sid,$afid);
  }else{
    while($tempdef>1){
      for($x=1; $x<sizeof($ashiparray)+1; $x++){
        if($ashiparray[$x]>0){
          $tempdef = $tempdef - getShipStat(hp,$x);
          if($tempdef>0){
            $ashiparray[$x]--;
          }else{
            break;
          }
        }
      }
      if($count==sizeof($ashiparray)){
        break;
      }
    }
    for($x=1; $x<sizeof($ashiparray)+1; $x++){
      $str = "ship".$x;
      setFleetStat($str,$ashiparray[$x],$sid,$afid);
    }
  }
  
  //The planet was conquered so change owner
  setPlanetStat(ownerid,getFleetStat(ownerid,$sid,$afid),$sid,$dpid);
  setPlanetStat(lastshipq,0,$sid,$dpid);
  $query = "DELETE FROM shipq$sid WHERE pid=$dpid";
  $result = @mysql_query($query);
  
}else{
  //Destroy losers fleet
  $endstr1 = 'You attacked <a href="./planetdetails.php?id='.$dpid.'">'.getPlanetStat(name,$sid,$dpid).'</a> with the fleet '.getFleetStat(name,$sid,$afid).'. Your attack lost with '.$attack.' attack to '.$defence.' defence.';
  $endstr2 = 'Your planet <a href="./planetdetails.php?id='.$dpid.'">'.getPlanetStat(name,$sid,$dpid).'</a> was attacked by <a href="./profile.php?id='.$aplayerid.'">'.getUserStat(username,$aplayerid).'</a>. You defended with '.$defence.' defense to '.$attack.' attack.';
  destroyFleet($sid,$afid);
  
  echo $endstr1;
  $tempdef = $attack;
  //For total dmg, do dmg to an attacking ship, if the saved dmg is enough to destroy the ship,
  //subtract it from the shiparraycounter then at the end update.

  //Check if the dmg is more than the hp of the fleet
  $dahp = calchp($sid,$dfid);
  echo $dahp;
  if($dahp<$tempdef){
    destroyFleet($sid,$dfid);
  }else{
    while($tempdef>1){
      for($x=1; $x<sizeof($dshiparray)+1; $x++){
        if($dshiparray[$x]>0){
          $tempdef = $tempdef - getShipStat(hp,$x);
          if($tempdef>0){
            $dshiparray[$x]--;
          }else{
            break;
          }
        }
      }
      if($count==sizeof($ashiparray)){
        break;
      }
    }
    for($x=1; $x<sizeof($dshiparray)+1; $x++){
      $str = "ship".$x;
      setFleetStat($str,$dshiparray[$x],$sid,$dfid);
    }
  }
}

addNotification($endstr1, $sid, $aplayerid);
addNotification($endstr2, $sid, $dplayerid);
}
}
}
?>
      