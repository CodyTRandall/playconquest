<?php /**/ ?><?php

include('./header.php');

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
  return $num+getPlanetStat(cannon,$sid,$pid);
}
if(!isset($sid)){
  echo 'Error. You need to be logged into a server for this';
  exit();
}

echo '
<script language="Javascript">
var change = function(x){

   for(var y=0; y<5; y++){
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
<a href="javascript:;" onClick="change(0)">Overview</a> | 
<a href="javascript:;" onClick="change(1)">Resources</a> |
<a href="javascript:;" onClick="change(2)">Buildings</a> | 
<a href="javascript:;" onClick="change(4)">Construction</a> | 
<a href="javascript:;" onClick="change(3)">Defense</a>
</center><br>';
/***************
GENERAL TAB
***************/
echo '<div id="0" style="display:block"><table cellspacing=5 align="center">
    <tr>
      <td><b>Galaxy</b></td>
      <td><b>Name</b></td>
      <td><b>Land</b></td>
      <td><b>Civs</b></td>
      <td><b>Elin.</b></td>
      <td><b>Cylite</b></yd>
      <td><b>Plexi</b></td>
      <td><b>Building</b></td>
      <td><b>Ship</b></td>
      <td><b>Build</b></td>
    </tr>';

$query = "SELECT id,x,y,z,civs,maxcivs,name,steel,plexi,cylite,land,landused,constructionid,lastshipq,land,landused FROM planets$sid WHERE ownerid=$id ORDER BY z ASC";
$result = @mysql_query($query);
while($row = mysql_fetch_array($result,MYSQL_ASSOC)){

  //Change some variables into strings
  $pid = $row[id];
  if($row[constructionid] == 0){
    $name = "None";
  }else{
    $name = getBuildStat(name,$row[constructionid]);
  }
  $sname = "None";
  if($row[lastshipq]>0){
    $sname = "Yes";
  }
  
  
  //Check if a new table needs to be printed because we are in a different z
  if($lastz != $row[z]){
  
    //Print the new galaxy name followed by a table
     $lastz = $row[z];
  }
    //Get the new galaxys name
    $query2 = "SELECT name FROM galaxy$sid WHERE id=".$row[z];
    $result2 = mysql_query($query2);
    $row2 = mysql_fetch_array($result2);
    
  echo '<tr>
      <td><a href="./map.php?gid='.$row[z].'">'.$row2[0].'</a></td>
      <td><a href="./name.php?id='.$row[id].'">'.$row[name].'</a></td>
      <td>'.$row[land].'</td>
      <td>'.$row[civs].'</td>
      <td>'.$row[steel].'</td>
      <td>'.$row[cylite].'</td>
      <td>'.$row[plexi].'</td>
      <td>'.$name.'</td>
      <td>'.$sname.'</td>
      <td><a href="./build.php?id='.$row[id].'">Build</a></td>
    </tr>';
}
echo '</table></div>';
/**********
RESOURCES TAB
***********/
echo '<div id="1" style="display:none"><table cellspacing=5 align="center">
    <tr>
      <td><b>Name</b></td>
      <td><b>Land</b></td>
      <td><b>Civs [/hr]</b></td>
      <td><b>Elin [/hr]</b></td>
      <td><b>Cylite [/hr]</b></yd>
      <td><b>Plexi [/hr]</b></td>
      <td><b>Build</b></td>
    </tr>';

$query = "SELECT id,civs,maxcivs,name,steel,plexi,cylite,land,landused,constructionid,lastshipq,land,landused FROM planets$sid WHERE ownerid=$id ORDER BY z ASC";
$result = @mysql_query($query);
while($row = mysql_fetch_array($result,MYSQL_ASSOC)){  
    $pid=$row[id];
  echo '<tr>
      <td><a href="./name.php?id='.$row[id].'">'.$row[name].'</a></td>
      <td>'.$row[land].'/'.$row[landused].'</td>
      <td>'.$row[civs].'/'.$row[maxcivs].' ['.civProductionRate($sid,$pid).']</td>
      <td>'.$row[steel].' ['.resourceProductionRate(steel,$sid,$pid).']</td>
      <td>'.$row[cylite].' ['.resourceProductionRate(cylite,$sid,$pid).']</td>
      <td>'.$row[plexi].' ['.resourceProductionRate(plexi,$sid,$pid).']</td>
      <td><a href="./build.php?id='.$row[id].'">Build</a></td>
    </tr>';
}
echo '</table></div>';
/***********
BUILDINGS TAB
************/
echo '<div id="2" style="display:none"><table cellspacing=5 align="center">
    <tr>
      <td><b>Name</b></td>
      <td><b>Factory</b></td>
      <td><b>Refinery</b></td>
      <td><b>Distillery</b></td>
      <td><b>Barracks</b></yd>
      <td><b>Academy</b></td>
      <td><b>Fabrication</b></td>
      <td><b>Build</b></td>
    </tr>';

$query = "SELECT id,name,steelmod,pleximod,cylitemod,civmod,researchmod,plant FROM planets$sid WHERE ownerid=$id ORDER BY z ASC";
$result = @mysql_query($query);
while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
    
  echo '<tr>
      <td><a href="./name.php?id='.$row[id].'">'.$row[name].'</a></td>  
      <td>'.$row[steelmod].'</td>
      <td>'.$row[cylitemod].'</td>
      <td>'.$row[pleximod].'</td>
      <td>'.$row[civmod].'</td>
      <td>'.$row[researchmod].'</td>
      <td>'.$row[plant].'</td>
      <td><a href="./build.php?id='.$row[id].'">Build</a></td>
    </tr>';
}
echo '</table></div>';
/*********
CONSTRUCTION TAB
**********/
echo '<div id="4" style="display:none">
  <table cellspacing=5 align="center">
    <tr>
      <td><b>Name</b></td>
      <td><b>Land Left</b></td>
      <td><b>Current Construction</b></td>
      <td><b>Time Left</b></td>
      <td><b>Build</b></td>
    </tr>';
  $query = "SELECT id,name,constructionid,constructiontime,land FROM planets$sid WHERE ownerid=$id ORDER BY z ASC";
  $result = mysql_query($query);
  while($row = mysql_fetch_array($result)){
    $timeleft = max(0,($row[constructiontime]-time()));
    if($timeleft==0 && $row[constuctionid]>0){
      $timeleft = '<a href="./build.php?id='.$row[id].'&f=1">Complete</a>';
    }
    if($row[constructionid]==0){
      $thename="None";
    }else{
      $thename = getBuildStat(name,$row[constructionid]);
    }
    echo '<tr>
        <td><a href="./name.php?id='.$row[id].'">'.$row[name].'</a></td>
        <td>'.$row[land].'</td>
        <td>'.$thename.'</td>
        <td>'.$timeleft.'</td>
        <td><a href="./build.php?id='.$row[id].'">Build</a></td>
      </tr>';
  }
echo '</table></div>';
/*********
COMBAT TAB
**********/
echo '<div id="3" style="display:none"><table cellspacing=5 align="center">
    <tr>
      <td><b>Name</b></td>
      <td><b>Queue Size</b></td>
      <td><b>Current Ship</b></td>
      <td><b>Time Left</b></td>
      <td><b>Planet Defense</b></td>
      <td><b>Build</b></td>
    </tr>';

$query = "SELECT id,name FROM planets$sid WHERE ownerid=$id ORDER BY z ASC";
$result = @mysql_query($query);
while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
  $pid = $row[id];
  $query2 = "SELECT endtime,shiptype FROM shipq$sid WHERE pid=$pid AND ownerid=$id ORDER BY endtime ASC";
  $result2 = mysql_query($query2);
  $queuesize = mysql_num_rows($result2);
  if($row2 = mysql_fetch_array($result2)){
    $finish = $row2[endtime]-time();
    $finish = max(0,$finish);
    echo '<tr>
      <td><a href="./name.php?id='.$row[id].'">'.$row[name].'</a></td>  
      <td>'.$queuesize.'</td>
      <td>'.getShipStat(name,$row2[shiptype]).'</td>
      <td>'.$finish.'</td>
      <td>'.calcDef($sid,$row[id]).'</td>
      <td><a href="./build.php?id='.$row[id].'">Build</a></td>
    </tr>';
  }else{
    echo '<tr>
      <td><a href="./name.php?id='.$row[id].'">'.$row[name].'</a></td>  
      <td>0</td>
      <td>---</td>
      <td>---</td>
      <td>'.calcDef($sid,$row[id]).'</td>
      <td><a href="./build.php?id='.$row[id].'">Build</a></td>
    </tr>';
  }
}
echo '</table></div>';

include('./footer.php');
?>