<?php

require_once('./www.playconquest.com/db_connect.php');

$servercount = getServerNumber();
for($x=1; $x<$servercount+1; $x++){
	$query = "SELECT id FROM fleet$x WHERE destination>0 AND combatid=0";
	$result = @mysql_query($query);
	$sid = $x;
	while($row = mysql_fetch_array($result)){
	
		landFleet($sid,$row[id]);

	}
}
?>