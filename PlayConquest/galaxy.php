<?php

include('./header.php');

if(!isset($sid)){
  echo 'Error, you are not logged into a server';
  exit();
}

//Start Javascript
echo '<Script language="Javascript">';

//Define the query for System information
$query = "SELECT id,name,x,y,ownerpid,con1,con2,con3,type FROM galaxy$sid";
$result = mysql_query($query);
$rowNumber = mysql_num_rows($result);
$counter = 0;

//Define Javascript Array
//Id, name, x, y, owner
//define the system class
echo 'var systems = new Array();';
echo'
function system(id,name,x,y,ownerid,ownername,con1,con2,con3,img,playerimg,enemy){
  this.x = x;
  this.y = y;
  this.id = id;
  this.con1 = con1;
  this.con2 = con2;
  this.con3 = con3;
  this.name = name;
  this.ownerid = ownerid;
  this.ownername = ownername;
  this.active = false;
  this.img = img;
  this.color = playerimg;
  this.enemy = enemy;
}

var color=1;

function changeColor(x){
  color = x;
}';


while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
  //Create a system object for each item in db
  $pid = $row[ownerpid];
  if($pid!=0){
    $name = getPlanetStat(name,$sid,$pid);
  }else{
    $name = 'None';
  }
  //Get the players color if applicable
  if($pid>0){
    $color = getUserStat(color,getPlanetStat(ownerid,$sid,$pid));
  }else{
    $color = -1;
  }
  
  //1 = RED
  //2 = YELLOW
  //3 = GREEN
  $enemy = 0;
  $query2 = "SELECT ownerid FROM planets$sid WHERE z=".$row[id];
  $result2 = mysql_query($query2);

  while($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
    echo '//'.$row2[ownerid].'
   ';
    if($row2[ownerid] !=0){
      if($row2[ownerid] != $id){
        if($enemy==3){
          $enemy=2;
          break;
        }
        $enemy = 1;
      }
      if($row2[ownerid] == $id){
        if($enemy==1){
          $enemy=2;
          break;
        }
        $enemy=3;
      }
    }
  }
  echo 'systems['.$counter.'] = new system('.$row[id].',"'.$row[name].'",'.$row[x].','.$row[y].','.$pid.',"'.$name.'",'.$row[con1].','.$row[con2].','.$row[con3].','.$row[type].','.$color.','.$enemy.');';
  $counter++;
}

//end js declaration

//end js
echo '</script>';

//Print Canvas
echo '<canvas id="c"></canvas><script src="galaxy.js"></script>';

//Print the canvas menu
echo '<center>
<a href="javascript:;" onClick="changeColor(0)">Star Colors</a> | 
<a href="javascript:;" onClick="changeColor(3)">Your Systems</a> | 
<a href="javascript:;" onClick="changeColor(2)">Player Colors</a> | 
<a href="javascript:;" onClick="changeColor(1)">Default Colors</a></center>';

//Print the action menu
echo '<table cellspacing=5 cellpadding=5 align=center>
    <tr>
      <td><b>Name:</b></td>
      <td><div id="name"></div></td>
    </tr><tr>
      <td><b>Coords:</b></td>
      <td>(<span id="x">0</span>,<span id="y">0</span>)</td>
    </tr><tr>
      <td><b>Owner:</b></td>
      <td><div id="owner"></div></td>
    </tr>
  </table>';
  
include('./footer.php');
?>