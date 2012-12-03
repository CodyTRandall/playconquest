<?php /**/ ?><?php

include('./header.php');

echo '<table align="center" cellspacing="5">
    <tr>
      <td></td>
      <td><b>Name</b></td>
      <td><b>Points</b></td>
    </tr>';

$count = 0;

$query = "SELECT id,s1,s2 FROM users WHERE s1=$sid OR s2=$sid";
$result = mysql_query($query);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
  
  for($x=1; $x<getMaxServer()+1; $x++){
    $str = 's'.$x;
    $number = getUserStat($str,$row[id]);
    if($number == $sid){
      $number = $x;
      break;
    }
  }
  $str = 'points'.$number;
  $points = getUserStat($str,$row[id]);

  $arr[$row[id]] = $points;
}

arsort($arr);

foreach ($arr as $key=>$val){
  if($count>100){
    break;
  }
  $count++;
  echo '<tr>
      <td>'.$count.'.</td>
      <td><a href="./profile.php?id='.$key.'">'.getUserStat(username,$key).'</a></td>
      <td>'.$val.'</td>
      </tr>';
}
echo '</table>';
include('./footer.php');

?>
  