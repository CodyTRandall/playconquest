<?php /**/ ?><?php

include('./header.php');

if(!isset($sid)){
  echo 'err';
  exit();
}

function researchImage($rid){
    return '<A href="./research.php?id='.$rid.'" onMouseOver="showObject(event,\''.$rid.'go\');" onMouseOut="hideObject(\''.$rid.'go\');"><img src="http://www.playconquest.com/woc/items/33.png"></a>';
}

function rph($sid,$id){
  $return = 0;
  $query = "SELECT researchmod FROM planets$sid WHERE ownerid=$id";
  $result = mysql_query($query);
  while($row = mysql_fetch_array($result)){
    $return += $row[researchmod];
  }
  return $return;
}

if(isset($_GET['id'])){
  
  $rid = escape_data($_GET['id']);

  $sub = getResearchStat(sub,$rid);
  $pass = false;
  if($sub==0){
    $pass = true;
  }
  
  //check to see if you have already researcheed it
  $query = "SELECT id FROM tech$sid WHERE ownerid=$id AND techid=$rid";
  $result = mysql_query($query);
  if($row = mysql_fetch_array($result)){
    echo ' You have already researched this.<br><br>';
  }else{
  
    //check you have requirements
    $query = "SELECT id FROM tech$sid WHERE techid=$sub AND ownerid=$id";
    $result = mysql_query($query);
    if($row = mysql_fetch_array($result) || $pass){
      //This checks points
      if(addResearch($rid,$sid,$id)){
        echo 'You have researched '.getResearchStat(name,$rid).'.<br><br>';
      }else{
        echo 'You do not have enough rp to research that.<br><br>';
      }
    }else{
      echo 'You need to research '.getResearchStat(name,$sub).' before you can research this.<br><br>';
    }
  }
}
  
echo 'Research Points: '.getPoints($sid,$id).' ['.rph($sid,$id).'/hr]<br><br>';

//Start Javascript
echo '<Script language="Javascript">';



//Define Javascript Array
//Id, name, x, y, owner
//define the system class
echo 'var systems = new Array();';
echo'
function system(id,name,x,level,desc,tree,points,tech,has){
  this.x = x;
  this.name=name;
  this.level = level;
  this.id = id;
  this.desc = desc;
  this.tree = tree;
  this.points = points;
  this.active = false;
  this.type = tech;
  this.has = has;
}

var color=1;

function changeColor(x){
  color = x;
}';

//Define the query for System information
$query = "SELECT id,name,level,tree,x,description,points,type FROM mastertech";
$result = mysql_query($query);
$rowNumber = mysql_num_rows($result);
$counter = 0;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
  $query2 = "SELECT techid FROM tech$sid WHERE techid=$row[id] AND ownerid=$id";
  $result2 = mysql_query($query2);
  if($row2 = mysql_fetch_array($result2)){
    $has="true";
  }else{
    $has="false";
  }
  echo 'systems['.$counter.'] = new system('.$row[id].',"'.$row[name].'",'.$row[x].','.$row[level].',"'.$row[description].'",'.$row[tree].','.$row[points].','.$row[type].',"'.$has.'");';
  $counter++;
}

//end js declaration

//end js
echo '</script>';

//Print Canvas
echo '<canvas id="c"></canvas><script src="research.js"></script>';

echo '<br><br><table>
    <tr><div id="name">Nothing Selected</div></tr>
    <tr><div id="points"></div></tr>
    <tr><div id="description"></div></tr></table>';
include('./footer.php');
?>
  