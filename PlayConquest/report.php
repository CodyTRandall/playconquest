<?php /**/ ?><?php

include('./header.php');

if(isset($_GET[id])){

	$fid = (integer) escape_data($_GET[id]);
	
	//check if owner
	if(getFleetStat(ownerid,$sid,$fid)!=$id){
		echo 'err';
		exit();
	}	
}else{
	echo 'err';
	exit();
}

$query = "SELECT report,probetime FROM fleet$sid WHERE id=$fid";
$result = mysql_query($query);
$row = mysql_fetch_array($result);

if($row[1]>0){
	echo 'There is no report for this fleet.';
}else{
	echo $row[0];
}

include('./footer.php');

?>