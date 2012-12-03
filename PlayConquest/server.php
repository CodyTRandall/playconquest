<?php /**/ ?><?php

include('./header.php');
  
//Check to see if you are already logged into a server
if(isset($sid)){
  echo 'Error';
  exit();
}

//Check to see if the user is trying to join a server or enter a server
if(isset($_GET['s'])){

  $sid = escape_data($_GET['s']);
  
  if(isOnServer($sid,$id)){
    //Log into server
    $_SESSION['sid'] = $sid;
    changePage('./galaxy.php');
  }else{
    //Enter the server
    if(getServerStat(users,$sid) < getServerStat(maxusers,$sid)){
      $users = getServerStat(users,$sid);
      $users++;
      $query = "UPDATE serverlist SET users=$users WHERE id=$sid";
      $result = @mysql_query($query);
      if($result){
        $bool = false;
        for($x=1;$x<3;$x++){
          $string = "s".$x;
          $serverid = getUserStat($string, $id);
          if($serverid == 0){
            $query = "UPDATE users SET $string=$sid WHERE id=$id";
            $result = @mysql_query($query);
            $bool = true;
            break;
          }
        }
        if($bool){
          //Create a planet on this server
          $z = rand(1,getServerStat(maxz,$sid));
          $x = rand(0,500);
          $y = rand(0,500);
          $img = rand(1,10);
          $name = getUserStat(username,$id);
          $query = "INSERT INTO planets$sid(name,x,y,z,landused,ownerid,img,starttime) VALUES('$name',$x,$y,$z,1000,$id,$img,".time().")";
          $result = @mysql_query($query);
          $query = "SELECT id FROM planets$sid WHERE ownerid=$id";
          $result = mysql_query($query);
          $row = mysql_fetch_array($result);
          $_SESSION['sid'] = $sid;
          changePage('./build.php?id='.$row[0]);
        }else{
          echo 'err';
          exit();
        }
      }
      
    }else{
      echo 'Sorry that server is full';
      include('./footer.php');
      exit();
    }
  }
}
function gameTypeToText($type){
  if($type==0){
    return 'King of the Hill';
  }
  if($type==1){
    return 'Conquerer';
  }
  if($type==2){
    return 'Time';
  }
}

//Print the list of servers
echo '<table align="center" cellspacing=5 cellpadding=5>
    <tr>
      <td><b>Name</b></td>
      <td><b>Users</b></td>
      <td><b>Map</b></td>
      <td><b>Game</b></td>
      <td><b>Status</b></td>
      <td><b>Enter</b></td>';
$query = "SELECT id,servername,users,maxusers,status,map,gametype FROM serverlist";
$result = @mysql_query($query);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){

  echo '<tr>
      <td>'.$row['servername'].'</td>
      <td>'.$row['users'].'/'.$row['maxusers'].'</td>
      <td>'.mapType($row['map']).'</td>
      <td>'.gameTypeToText($row[gametype]).'</td>
      <td>';
    
    if($row['status']!=2){
    if($row['status']==0){
      echo '<font color="lime">Online</font></td>';
      }else{
    echo '<font color="Orange">In Progress</font></td>';
   }
      if(isOnServer($row['id'],$id)){
        echo '<td><a href="./server.php?s='.$row['id'].'">Enter</a></td>';
      }else{
        if(getServerStat(users,$row['id']) < getServerStat(maxusers,$row['id']) && $row['status']==0){
          echo '<td><a href="./server.php?s='.$row['id'].'">Join</a></td>';
        }else{
          echo '<td>Full</td>';
        }
      }
      
    }else{
      echo '<font color="red">Offline</font></td>';
    }
}
echo '</table>';
include('./footer.php');

?>