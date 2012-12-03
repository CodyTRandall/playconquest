<?php /**/ ?><HTML>
<head>
<meta name="description" content="Conquest, a free browser based strategic warfare game! Conquer your enemies in space, take over planets and rule the galaxy!" />
<meta name="keywords" content="conquest,play,game,free,browser,based,rpg,strategic,warfare,mmo" />
<meta name="author" content="Cody Randall" />
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
</head>
</html>
<?php
session_start();

//Include the header
include('./header.php');

echo '<b><u>News</b></u><br>
  <br>Server Status ';

$query = "SELECT downtime FROM admin";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
if($row[0]>0){
  echo '<font color="red">DOWN FOR PATCHING</font>';
}else{
  echo '<font color="lime">ONLINE</font>';
}
  echo '<br><br>';

$query = "SELECT date,text,img FROM news ORDER BY date DESC LIMIT 5";
$result = mysql_query($query);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
  if($row[img]>0){
    echo '<img src="./newsimg/'.$row[img].'.JPG"><br>';
  }
  
  echo '<b><u>'.$row[date].'</b></u><br>
  <br>
  '.$row[text].'<br>
  <hr>';
}


include('./footer.php');
?>