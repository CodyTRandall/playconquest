<?php

require_once('./www.playconquest.com/db_connect.php');
//require_once('./db_connect.php');

$servercount = getServerNumber();
for($x=1; $x<$servercount+1; $x++){
	$query = "SELECT researchmod,ownerid FROM planets$x WHERE researchmod>0";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
                $points = getPoints($x,$row[ownerid]);
                $points += $row[researchmod];
		setPoints($points,$x,$row[ownerid]);
	}
		
}

?>