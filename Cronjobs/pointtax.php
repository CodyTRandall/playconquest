<?php

require_once('./www.playconquest.com/db_connect.php');
//include('./db_connect.php');


$servercount = getServerNumber();
for($x=1; $x<$servercount+1; $x++){
	$query = "SELECT ownerpid FROM galaxy$x WHERE ownerpid>0";
	$result = @mysql_query($query);
	$sid=$x;

	while($row = mysql_fetch_array($result)){
		$pid = $row[0];
		$id = getPlanetStat(ownerid,$sid,$pid);

		for($y=1; $y<getMaxServer()+1; $y++){
			$str = 's'.$y;
			$number = getUserStat($str,$id);
			if($number == $sid){
				$number = $y;
				break;
			}
		}
		
		$str = 'points'.$number;
		$points = getUserStat($str,$id);
		$points++;
		echo $str;
		$query2 = "UPDATE users SET $str=$points WHERE id=$id";
		$result2 = @mysql_query($query2);
	}
}
?>