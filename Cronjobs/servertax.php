<?php

require_once('./www.playconquest.com/db_connect.php');

$query3 = "SELECT id,maxpoints,starttime,jointime,maxz FROM serverlist";
$result3 = mysql_query($query3);
while($row = mysql_fetch_array($result3)){

  $sid = $row[id];
  
  //Check if people can still join the server
  if($row[starttime]+$row[jointime]<time()){
    $query2 = "UPDATE serverlist SET status=1 WHERE id=$sid";
    $result2 = @mysql_query($query2);
  }
  
  //Check if the server needs to restart because someone won, get all the points from each player
  $query2 = "SELECT id,s1,s2 FROM users WHERE s1=$sid OR s2=$sid";
  $result2 = mysql_query($query2);
  while($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
  
    for($x=1; $x<getMaxServer()+1; $x++){
      $str = 's'.$x;
      $number = getUserStat($str,$row2[id]);
      if($number == $sid){
        $number = $x;
        break;
      }
    }
    $str = 'points'.$number;
    $points = getUserStat($str,$row2[id]);
    
    $arr[$row2[id]] = $points;
  }
  
  arsort($arr);

  //Key is id, val is points
  $first=0;
  $second=0;
  $third=0;
  $go = false;
  foreach ($arr as $key=>$val){
    if(($val > $row[maxpoints]) || $first!=0){
      if($first == 0){
        $first = $key;
      }else if($second == 0){
        $second = $key;
      }else{
        $third = $key;
        $go = true;
        break;
      }
    }else{
      break;
    }
  }
  
  //If the server is restarting because someone won
  if($go){
    
    //Add the wins to the users
    $firstw = getUserStat(wins,$first);
    $firstw++;
    $query2 = "UPDATE users SET wins=$firstw WHERE id=$first";
    $result2 = @mysql_query($query2);
  
    //Add second
    $secondw = getUserStat(second,$second);
    $secondw++;
    $query2 = "UPDATE users SET second=$secondw WHERE id=$second";
    $result2 = @mysql_query($query2);
  
    //Add third
    $thirdw = getUserStat(third,$third);
    $thirdw++;
    $query2 = "UPDATE users SET third=$thirdw WHERE id=$third";
    $result2 = @mysql_query($query2);
  
    //Delete the fleet table, planets table, shipq and tech tables
    $query = "TRUNCATE planets$sid";
    $result = @mysql_query($query);
    $query = "TRUNCATE fleet$sid";
    $result = @mysql_query($query);
    $query = "TRUNCATE shipq$sid";
    $result = @mysql_query($query);
    $query = "TRUNCATE tech$sid";
    $result = @mysql_query($query);
    
    //Reset the Galaxy Table
    $query = "UPDATE galaxy$sid SET ownerpid=0";
    $result = @mysql_query($query);
    
    //Reset the serverlist
    $query = "UPDATE serverlist SET users=0,starttime=".time()." WHERE id=$sid";
    $result = @mysql_query($query);
    
    //Reset the users table
    $query2 = "SELECT id,s1,s2 FROM users WHERE s1=$sid OR s2=$sid";
    $result2 = mysql_query($query2);
    while($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
  
      for($x=1; $x<getMaxServer()+1; $x++){
        $str = 's'.$x;
        $number = getUserStat($str,$row2[id]);
        if($number == $sid){
          $number = $x;
          break;
        }
      }
      
      $query = "UPDATE users SET s$number=0, points$number=0, rp$number=0 WHERE id=".$row2[id]."";
      $result = @mysql_query($query);
    }
    
    //Populate the planets table
    for($x=1;$x<$row[maxz]+1;$x++){
      $query = "SELECT name FROM galaxy$sid WHERE id=$x";
      $result = mysql_query($query);
      $row2 = mysql_fetch_array($result);
      $thename = $row2[0];
      $planetnum = rand(1,5);
      
      for($y=0;$y<$planetnum;$y++){
        $randx = rand(20,680);
        $randy = rand(20,480);
        $type = rand(1,10);
        $land = rand(5,20)*100;
    
        if($y==0){
          $pname = $thename.' I';
        }else if($y==1){
          $pname = $thename.' II';
        }else if($y==2){
          $pname = $thename.' III';
        }else if($y==3){
          $pname = $thename.' IV';
        }else if($y==4){
          $pname = $thename.' V';
        }else if($y==5){
          $pname = $thename.' VI';
        }
    
        $query = "INSERT INTO planets$sid(name,x,y,img,land,landused,z,steel,cylite,plexi) VALUES ('$pname',$randx,$randy,$type,$land,$land,$x,0,0,0)";
        $result = @mysql_query($query);

      }
    }
    
    //Change the server status to open
    $query = "UPDATE serverlist SET status=0 WHERE id=$sid";
    $result = @mysql_query($query);
  }
}  