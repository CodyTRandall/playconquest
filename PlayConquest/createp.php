<?php

include('./db_connect.php');

$filename = "./names.txt";
$fd = fopen ($filename, "r");
$contents = fread($fd,filesize($filename));
fclose($fd);
$delimeter = ",";
$name = explode($delimeter,$contents);
echo sizeof($name);
$query = "TRUNCATE planets2";
$result = @mysql_query($query);
$query = "TRUNCATE galaxy2";
$result = @mysql_query($query);
echo mysql_error();

for($x=1;$x<46;$x++){
  $randx = rand(20,680);
  $randy = rand(20,480);
  $type = rand(0,5);
  $randname = rand(0,sizeof($name));
  $thename = $name[$randname];
  $query = "SELECT id FROM galaxy2 WHERE name='$thename'";
  $result = mysql_query($query);
  while($row = mysql_fetch_array($result)){
  echo mysql_error();
    $randname = rand(0,sizeof($name));
    $thename = $name[$randname];
    $query = "SELECT id FROM galaxy2 WHERE name='$thename'";
    $result = mysql_query($query);
  }
  $query = "INSERT INTO galaxy2(name,x,y,type) VALUES ('$thename',$randx,$randy,$type)";
  $result = @mysql_query($query);

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
    
    $query = "INSERT INTO planets2(name,x,y,img,land,landused,z,steel,cylite,plexi) VALUES ('$pname',$randx,$randy,$type,$land,$land,$x,0,0,0)";
    $result = @mysql_query($query);

  }
  
}

?>