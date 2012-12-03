<?php
$host=//edited
$user=//edited
$password=//edited
$dbc= mysql_connect($host, $user, $password) OR die('No connect');
@mysql_select_db (/*edited*/) OR die('No DB');

/**************
MASTER FUNCTION LIST
GENERAL FUNCTIONS
***************/
function escape_data($data){
  global $dbc;
  if(ini_get('magic_quotes_gpc')){
    $data=stripslashes($data);
  }
  $data=htmlspecialchars($data);
  return mysql_real_escape_string(trim($data), $dbc);
}
function getMaxServer(){
  return 2;
}
function addNotification($notification,$sid,$id){
  $query = "INSERT INTO notifications(thetext,sid,ownerid) VALUES('$notification',$sid,$id)";
  $result = @mysql_query($query);
  return 1;
}

function getUpkeepCost($sid,$pid){
  $query = "SELECT landused,land FROM planets$sid WHERE id=$pid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  return 0;
  return $row[0]-$row[1]*10;
}

function getOutput($sid,$pid){
  return 0;
}
function mapType($map){
  if($map==0){
    return "Spiral";
  }if($map==1){
    return "Cluster";
  }if($map==2){
    return "Barred";
  }if($map==3){
    return "Random";
  }if($map==4){
    return "Whirlpool";
  }if($map==5){
    return "Ring";
  }
}

function notificationCount($sid,$id){
  $query = "SELECT id FROM notifications WHERE ownerid=$id AND sid=$sid AND readcount=0";
  $result = @mysql_query($query);
  return mysql_num_rows($result);
}

function isOnServer($sid, $id){
  for($x=1; $x<getServerNumber()+1; $x++){
    $query = "SELECT s$x FROM users WHERE id=$id";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    if($row[0] == $sid){
      return true;
    }
  }
  return false;
}

function getServerNumber(){
  $query = "SELECT id FROM serverlist";
  $result = @mysql_query($query);
  return mysql_num_rows($result);
}

function getUserStat($stat,$id){
  $query = "SELECT $stat FROM users WHERE id=$id";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  return $row[0];
}

function changePage($url){
  echo '<meta http-equiv="REFRESH" content="0;url='.$url.'">';
}

function getServerStat($stat,$id){
  $query = "SELECT $stat FROM serverlist WHERE id=$id";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);

  return $row[0];
}
function setPlanetStat($stat,$num,$sid,$pid){
  $query = "UPDATE planets$sid SET $stat='$num' WHERE id=$pid";
  $result = @mysql_query($query);
  if($result){
    return true;
  }
  return false;
}

function getBuildStat($stat,$id){
  $query = "SELECT $stat FROM masterbuild WHERE id=$id";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  return $row[0];
}

function resourceProductionRate($stat,$sid,$pid){
  //get the base amount
  $query = "SELECT ".$stat."mod FROM planets$sid WHERE id=$pid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  $num = floor($row[0]*getPlanetStat(civs,$sid,$pid)*.5);
  $img = getPlanetStat(img,$sid,$pid);
  
  //add the planet type
  $query = "SELECT $stat FROM masterplanets WHERE id=$img";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  $num *= $row[0];
  
  //add any applicable research
  if($stat=="cylite"){
    $num *= getResearch(1,$sid,getPlanetStat(ownerid,$sid,$pid));
  }
  if($stat=="plexi"){
    $num *= getResearch(5,$sid,getPlanetStat(ownerid,$sid,$pid));
  }
  if($stat=="steel"){
    $num *= getResearch(10,$sid,getPlanetStat(ownerid,$sid,$pid));
  }
  return floor($num);
}
function civProductionRate($sid,$pid){
  $query = "SELECT civs,civmod FROM planets$sid WHERE id=$pid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  $num = ceil($row[0]/10 * $row[1]);
  $img = getPlanetStat(img,$sid,$pid);
  $query = "SELECT civs FROM masterplanets WHERE id=$img";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  $num *= $row[0];
  
  //add the research
  $num *= getResearch(13,$sid,getPlanetStat(ownerid,$sid,$pid));
  return floor($num);
}
function timeLeft($timestamp){
  return $timestamp-time();
}

function getPlanetStat($stat,$sid,$pid){
  $query = "SELECT $stat FROM planets$sid WHERE id=$pid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
        echo mysql_error();
  return $row[0];
}
function getCost($stat,$bid,$sid,$pid){
  if($stat == 'land'){
    return getBuildStat(landcost,$bid);
  }
  $stat = $stat.'cost';
  $basecost = getBuildStat(basecost,$bid);
  if($stat == 'civscost'){
    $basecost = 0;
  }
  $cost = getBuildStat($stat,$bid);
  return floor($basecost+$cost*pow(getPlanetStat(getBuildStat(increases,$bid),$sid,$pid),getBuildStat(costmod,$bid)));
}
function canAfford($stat,$bid,$sid,$pid){
  if(getCost($stat,$bid,$sid,$pid) > getPlanetStat($stat,$sid,$pid)){
    return false;
  }
  return true;
}
function shipQueueFull($sid,$pid,$id){
  $plantlvl = getPlanetStat(plant,$sid,$pid);
  $query = "SELECT id FROM shipq$sid WHERE ownerid=$id AND pid=$pid";
  $result = mysql_query($query);
  if($plantlvl*5+5>mysql_num_rows($result)){
    return false;
  }
  return true;
}
function getFleetStat($stat,$sid,$fid){
  $query = "SELECT $stat FROM fleet$sid WHERE id=$fid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  return $row[0];
}
function setFleetStat($stat,$amt,$sid,$fid){
  $query = "UPDATE fleet$sid SET $stat='$amt' WHERE id=$fid";
  $result = @mysql_query($query);
  if($result){
    return true;
  }
  return false;
}
function calcBuildTime($bid,$sid,$pid){
  //If it is a biodome divide by 50
  $build = getPlanetStat(getBuildStat(increases,$bid),$sid,$pid);
  if($bid == 1){
    $build /= 50;
  }
  $time = pow($build,getBuildStat(buildtimemod,$bid));
  $time *= getBuildStat(buildtime,$bid);
  //calc research
  $subtract = $time*getResearch(12,$sid,getPlanetStat(ownerid,$sid,$pid))-$time;
  return floor($time-$subtract);
}
function getShipStat($stat,$sid){
  $query = "SELECT $stat FROM mastership WHERE id=$sid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  return $row[0];
}
function distance($x1,$y1,$x2,$y2){
  return sqrt(pow(($x2-$x1),2)+pow(($y2-$y1),2));
}

/****************
Travel time function is a modified non-recursive Dykstras algorithm to calculate
the travel time from one solar system to another
The graph is dynamic based on who has permissions to pass through other solar systems.
@returns, either time or -1, which means impossible
*******************/
function travelTime($sid,$pid,$did,$id){
  //Check if solar system travel
  if(getPlanetStat(z,$sid,$pid) == getPlanetStat(z,$sid,$did)){
    $time = 30*distance(getPlanetStat(x,$sid,$pid),getPlanetStat(y,$sid,$pid),getPlanetStat(x,$sid,$did),getPlanetStat(y,$sid,$did));
    
    //decrease with research
    $decrease = $time*getResearch(4,$sid,$id)-$time;
    return time()+floor($time-$decrease);
    
  }

  //Get the variables
  $destGalaxy = getPlanetStat(z,$sid,$did);
  $z = getPlanetStat(z,$sid,$pid);

  $queue[0] = $z;
  $marker = 0;
  $count = 0;
  $endNode = 0;
  $jumps = 0;
  $go = true;
  $owned = false;
  while($count<100){
    //Get the next element examining in the queue
    $z = $queue[$count];
    if($z<1){
      return -1;
    }
    //Check if we reached the end of one stage of the breadth
    if($endNode == $count){
      $endNode=0;
      $jumps++;
    }
    
    if($z == $destGalaxy){
      $time = $jumps*3600*3;
      $decrease = $time*getResearch(4,$sid,$id)-$time;
      return time()+floor($time-$decrease);
    }  
    
    //Increase the counter, this is counting the number search we are on for the queue
    $count++;
      
    //Check if this galaxy is passable
    $query = "SELECT ownerpid FROM galaxy$sid WHERE id=$z";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    if($row[0] > 0){
      $ownerid = getPlanetStat(ownerid,$sid,$row[0]);
      if($ownerid != $id){
        $owned = true;
      }
    }
    if(!$owned){
      //Get the first 3 elements that this node expands to
      $query = "SELECT con1,con2,con3 FROM galaxy$sid WHERE id=$z";
      $result = mysql_query($query);
      $row = mysql_fetch_array($result);
      //Check to see if we need to add them to the queue
      for($x=0; $x<3; $x++){
        if($row[$x] == $destGalaxy){
          //We have found the galaxy, return
          $time = $jumps*3600*3;
          $decrease = $time*getResearch(4,$sid,$id)-$time;
          return time()+floor($time-$decrease);
        }else if($row[$x] > 0){
          //Make sure this found node doesnt exist already
          for($y=0; $y<sizeOf($queue); $y++){
            if($queue[$y] == $row[$x]){
              $go = false;
              break;
            }
          }
          if($go){
            //Found another node, increase the queue size and add it to the end
            $marker++;
            $queue[$marker] = $row[$x];
          }
          $go=true;
        }
      }

      //Get alist of all  galaxys that point to this one that we havent done yet
      $query = "SELECT id FROM galaxy$sid WHERE con1=$z OR con2=$z OR con3=$z";
      $result = mysql_query($query);
      while($row2 = mysql_fetch_array($result)){
        //Check if we already used it
        if($row2[id]!=$z){
          if($row2[id] == $row[0] || $row2[id] == $row[1] || $row2[id] == $row[2]){
          }else{
            //Make sure this found node doesnt exist already
            for($y=0; $y<sizeOf($queue); $y++){
              if($queue[$y] == $row2[id]){
                $go = false;
                break;
              }
            }
            if($go){
              //This node is connected so add it to the queue
              $marker++;
              $queue[$marker] = $row2[id];
            }
            $go=true;
          }
        }
      }
      if($endNode==0){
        $endNode=$marker;
      }
    }else{
      $owned = false;
    }
  }
}

function fleetSize($sid,$fid){
  //Check if it is empty
  $query = "SELECT id FROM mastership";
  $result = mysql_query($query);
  $num = mysql_num_rows($result);
  $amt = 0;
  for($x=1; $x<$num+1; $x++){
    $str = 'ship'.$x;
    $amt += getFleetStat($str,$sid,$fid);

  }
  
  return $amt;

}
function moveFleet($fid,$sid,$pid,$did){
  
  //Pid is the planet coming from
  //Did is the destination id
  
  //Make sure the fleet has size
  if(fleetSize($sid,$fid)==0){
    return -1;
  }
  
  //Make sure the travel time is not -1, which means cant be visited
  $travelTime = travelTime($sid,$pid,$did,getFleetStat(ownerid,$sid,$fid));
  if($travelTime<1){
    return -2;
  }
  
  //Set variables
  setFleetStat(destination,$did,$sid,$fid);
  setFleetStat(destinationtime,$travelTime,$sid,$fid);
  setFleetStat(inport,0,$sid,$fid);
  
  //Check if they are going to be in combat, change combat id
  if((getPlanetStat(ownerid,$sid,$did)>0) && (getPlanetStat(ownerid,$sid,$did)!=getFleetStat(ownerid,$sid,$fid))){
    setFleetStat(combatid,1,$sid,$fid);
  }
  return 1;
}

function landFleet($sid,$fid){
  //Check the time to see if can land
  $destination = getFleetStat(destination,$sid,$fid);
  if(timeLeft(getFleetStat(destinationtime,$sid,$fid))<0){
    //$did is the owner id of the destination
    $did = getPlanetStat(ownerid,$sid,$destination);
    if(getFleetStat(combatid,$sid,$fid)>0){
      //Check to make sure the combat is still valid
      if($did == getFleetStat(ownerid,$sid,$fid) || $did == 0){
        setFleetStat(combatid,0,$sid,$fid);
      }else{
        return false;
      }
    }
    
    //Check and see if it has a settler landing on an uninhabited planet
    if(($did != getFleetStat(ownerid,$sid,$fid)) && ($did>0)){
      //Check to see if someone else owns the planet
      setFleetStat(combatid,1,$sid,$fid);
      return false;
    }
    
    setFleetStat(destination,0,$sid,$fid);
    setFleetStat(loc,$destination,$sid,$fid);
    
    if(getPlanetStat(ownerid,$sid,$destination) == 0){
      $settlers = getFleetStat(ship5,$sid,$fid);
      if($settlers>0){
        $settlers--;
        setFleetStat(ship5,$settlers,$sid,$fid);
        setPlanetStat(ownerid,getFleetStat(ownerid,$sid,$fid),$sid,$destination);
        addNotification('You have succesfully colonized <a href="planetdetails.php?id='.$destination.'">'.getPlanetStat(name,$sid,$destination).'</a>.',$sid,getFleetStat(ownerid,$sid,$fid));
      }
    }
    return true;
  }
  return false;
}

function addCargo($civs,$steel,$cylite,$plexi,$sid,$pid,$fid){
  $newcargo = calcCargo($civs,$steel,$cylite,$plexi);
  //Check if there is room on the fleet
  if(isRoom($newcargo,$sid,$fid)){
  
    //Check if the planet has valid resources
    $pcivs = getPlanetStat(civs,$sid,$pid);
    if($pcivs < $civs){
      return -2;
    }
    $psteel = getPlanetStat(steel,$sid,$pid);
    if($psteel < $steel){
      return -3;
    }
    $pcylite = getPlanetStat(cylite,$sid,$pid);
    if($pcylite < $cylite){
      return -4;
    }
    $pplexi = getPlanetStat(plexi,$sid,$pid);
    if($pplexi < $plexi){
      return -5;
    }
    
    //Subtract from the planet and update
    $pcivs -= $civs;
    setPlanetStat(civs,$pcivs,$sid,$pid);
    $psteel -= $steel;
    setPlanetStat(steel,$psteel,$sid,$pid);
    $pcylite -= $cylite;
    setPlanetStat(cylite,$pcylite,$sid,$pid);
    $pplexi -= $plexi;
    setPlanetStat(plexi,$pplexi,$sid,$pid);
    
    //Get the fleet amounts and increase then update
    $pcivs = getFleetStat(civs,$sid,$fid);
    $pcivs += $civs;
    setFleetStat(civs,$pcivs,$sid,$fid);
    
    $psteel = getFleetStat(steel,$sid,$fid);
    $psteel += $steel;
    setFleetStat(steel,$psteel,$sid,$fid);
    
    $pcylite = getFleetStat(cylite,$sid,$fid);
    $pcylite += $cylite;
    setFleetStat(cylite,$pcylite,$sid,$fid);
    
    $pplexi = getFleetStat(plexi,$sid,$fid);
    $pplexi += $plexi;
    setFleetStat(plexi,$pplexi,$sid,$fid);
    
    return 1;
  }
  return -1;  
}
function isRoom($amt,$sid,$fid){
  $current = currentCargo($sid,$fid);
  if(($current+$amt) > maxCargo($sid,$fid)){
    return false;
  }
  return true;
}
function calcCargo($civs,$steel,$cylite,$plexi){
  return $civs*5+$steel*2+$cylite+$plexi;
}
function currentCargo($sid,$fid){
  return calcCargo(getFleetStat(civs,$sid,$fid),getFleetStat(steel,$sid,$fid),getFleetStat(cylite,$sid,$fid),getFleetStat(plexi,$sid,$fid));
}
function maxCargo($sid,$fid){
  $query = "SELECT id FROM mastership";
  $result = @mysql_query($query);
  $num = mysql_num_rows($result);
  for($x=1; $x<$num+1; $x++){
    $str = 'ship'.$x;
    $total += getFleetStat($str,$sid,$fid)*getShipStat(cargo,$x);
  }
  
  //add research
  $total *= getResearch(8,$sid,getFleetStat(ownerid,$sid,$fid));
  return floor($total);
}
function getTypeStat($stat,$typeid){
  $query = "SELECT $stat FROM masterplanets WHERE id=$typeid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  return $row[0];
}
function addPoints($points,$sid,$id){
  $points += getPoints($sid,$id);
  $query = "UPDATE research$sid SET points=$points WHERE ownerid=$id";
  $result = @mysql_query($query);
}
function setPoints($points,$sid,$id){
  for($x=1; $x<getMaxServer()+1; $x++){
    $str = 's'.$x;
    $query = "SELECT $str FROM users WHERE id=$id";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    if($row[0]==$sid){
      $str = 'rp'.$x;
      $query = "UPDATE users SET $str=$points WHERE id=$id";
      $result = @mysql_query($query);
    }
  }
}

function getPoints($sid,$id){
  for($x=1; $x<getMaxServer()+1; $x++){
    $str = 's'.$x;
    $query = "SELECT $str FROM users WHERE id=$id";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    if($row[0]==$sid){
      $str = 'rp'.$x;
      $query = "SELECT $str FROM users WHERE id=$id";
      $result = mysql_query($query);
      $row = mysql_fetch_array($result);
      return $row[0];
    }
  }
  return -1;
}
function getResearchStat($stat,$rid){
  $query = "SELECT $stat FROM mastertech WHERE id=$rid";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  return $row[0];
}
function addResearch($rid,$sid,$id){
  //Get the points
  $points = getPoints($sid,$id);
  //Get the cost and check that the user has enough points
  $points -=  getResearchStat(points,$rid);
  if($points<0){
    return false;
  }
  //Add the research
  $type = getResearchStat(type,$rid);
  $query = "INSERT INTO tech$sid(ownerid,techid,type) VALUES($id,$rid,$type)";
  $result = @mysql_query($query);
  if($result){
    //Take the points
    setPoints($points,$sid,$id);
    return true;
  }else{
    return false;
  }
}

function getResearch($type,$sid,$id){
  $num = 1;
  $query = "SELECT techid FROM tech$sid WHERE ownerid=$id AND type=$type";
  $result = mysql_query($query);
  while($row = mysql_fetch_array($result)){
    $num += getResearchStat(amt,$row[0]);
  }
  return $num;
}

function calcShipBuildTime($shipid,$sid,$id){
  $time = getShipStat(buildtime,$shipid);
  $sub = $time*getResearch(9,$sid,$id)-$time;
  return floor($time-$sub);
}
function getSpyNum($sid,$fid){
  $query = "SELECT id FROM mastership";
  $result = mysql_query($query);
  $numship = mysql_num_rows($result);
  for($x=5; $x<$numship+1; $x++){
    $str = 'ship'.$x;  
    $num += getShipStat(spy,$x)*getFleetStat($str,$sid,$fid);
  }
  return $num;
}  
function defineHoverJs(){
  echo'
  <script language="JavaScript">
    ns4 = document.layers;  
    ie4 = document.all;  
    nn6 = document.getElementById && !document.all;  
 
    function hideObject(id) {  
      if (ns4) {  
        document.id.visibility = "hide";  
      }else if (ie4) {  
        document.all[id].style.visibility = "hidden";  
      }else if (nn6) {  
        document.getElementById(id).style.visibility = "hidden";  
      }  
    }

    function showObject(e,id) { 
      if (ns4) {  
        document.id.visibility = "show";  
        document.id.left = e.pageX;  
        document.id.top = e.pageY;  
      }else if (ie4) {  
        document.all[id].style.visibility = "visible";  
        document.all[id].style.left = e.clientX;  
        document.all[id].style.top = e.clientY;  
      }else if (nn6) {  
        document.getElementById(id).style.visibility = "visible";  
        document.getElementById(id).style.left = e.clientX;  
        document.getElementById(id).style.top = e.clientY;  
      }  
    }
  </script>';
}
function defineImage($rid,$sid,$id){
  $points = getPoints($sid,$id);
  $returnStr='<div id="'.$rid.'go" style="position:absolute; left:-180; top:-25; z-index:1; visibility:hidden;">
      <table bgcolor=black>
      <tr>
        <td>'.getResearchStat(name,$rid).' [';
    
    //Define the color for the points
    if($points<$pointcost){
      $returnStr.= '<font color=red>';
    }else{
      $returnStr.= '<font color=lime>';
    }
    
    $returnStr.= getResearchStat(points,$rid).'</font>]</td></tr>
    <tr>
      <td>'.getResearchStat(description,$rid).'</td>
    </tr>
      </table>
    </div>';
  return $returnStr;
}
function invitePoints($id){
  $invites = getUserStat(invites,$id);
  $votes = getUserStat(votes,$id);
  if($votes>10){
    $votes-=10;
    $invites++;
    $query = "UPDATE users SET invites=$invites,votes=$votes WHERE id=$id";
    $result = @mysql_query($query);
  }
  return $invites.'.'.$votes;
}
?>
