<?php

//include('./db_connect.php');
require_once('./www.playconquest.com/db_connect.php');

function getShipNumber(){
  $query = "SELECT id FROM mastership";
  $result = @mysql_query($query);
  return mysql_num_rows($result);
}

function calcDef($sid,$pid){
  $query = "SELECT id FROM fleet$sid WHERE loc=$pid AND destination=0";
  $result = mysql_query($query);
  while($row = mysql_fetch_array($result)){
    for($i=1; $i<getShipNumber()+1; $i++){
      $str = "ship".$i;
      $num += getShipStat(defend,$i)*getFleetStat($str,$sid,$row[id]);
    }
  }
  if(!($num>0)){
    $num=0;
  }
  return $num+getPlantStat(cannon,$sid,$pid);
}

$servercount = getServerNumber();
for($sid=1; $sid<$servercount+1; $sid++){
  $query = "SELECT id FROM fleet$sid WHERE probes>0";
  $result = @mysql_query($query);

  while($row = mysql_fetch_array($result)){

    //Get the probe fleet id
    $fid = $row[id];
    
    //Make sure time has elapsed and the person doesnt own the planet already
    $pid = getFleetStat(probes,$sid,$fid);
    
    if(timeLeft(getFleetStat(probetime,$sid,$fid))<1 && getFleetStat(ownerid,$sid,$fid)!=getPlanetStat(ownerid,$sid,$pid)){

      //Check if you already have a report
      if(getFleetStat(probetime,$sid,$fid) != -1){
        //Generate probe report
        $sentry = getPlanetStat(sentry,$sid,$pid);
        $probe = getSpyNum($sid,$fid);
        $land = getPlanetStat(landused,$sid,$pid)-getPlanetStat(land,$sid,$pid);
        
        if($probe<$sentry){
          $report = 'Your probes could not successfully penetrate the planet\'s radar scrambling.';
        }else if($probe>$sentry+40){
          $rand = rand(9,11)/10;
          $def = floor(calcDef($sid,$pid)*$rand);
          $report = 'Your probe report came back very positive. They report the estimated defensive capabilities are '.$def.'. The estimated land remaining is '.$land.'. This report is very accurate.';
        }else if($probe>$sentry+25){
          $rand = rand(8,12)/10;
          $def = floor(calcDef($sid,$pid)*$rand);
          $report = 'Your probe report came back positive. They report the estimated defensive capabilities are '.$def.'.';
        }else if($probe>$sentry+10){
          $rand = rand(7,13)/10;
          $def = floor(calcDef($sid,$pid)*$rand);
          $report = 'Your probe report came back neutral. They report the estimated defensive capabilities are '.$def.'. This report is somewhat uncertain.';
        }else{
          $rand = rand(5,15)/10;
          $def = floor(calcDef($sid,$pid)*$rand);
          $report = 'Your probe report came back very vague. They report the estimated defensive capabilities are '.$def.'. This report is very uncertain.';
        }
        echo $report;
        setFleetStat(report,$report,$sid,$fid);
        setFleetStat(probetime,-1,$sid,$fid);
        addNotification('Your scan of planet <a href="./planetdetails.php?id='.$pid.'">'.getPlanetStat(name,$sid,$pid).'</a> is complete. The results are <a href="./report.php?id='.$fid.'">here</a>', $sid, getFleetStat(ownerid,$sid,$fid));
      }
    }
  }
}
?>