<?php /**/ ?><?php

include('./header.php');

if(isset($_GET[id])){

	$fid = escape_data($_GET[id]);
	
	//Make sure you own
	if(getFleetStat(ownerid,$sid,$fid) != $id){
		echo 'err';
		exit();
	}
}else{
	echo 'err';
	exit();
}

if(isset($_POST['name'])){

	$name = ''.escape_data($_POST['name']);
	
	setFleetStat(name,$name,$sid,$fid);
	
	changePage('./fleet.php');
	
}

echo '<form action="./fname.php?id='.$fid.'" method="post">
		<input type="text" name="name" value="'.getFleetStat(name,$sid,$fid).'"> <input type="Submit" value="Change">';
	
	
include('./footer.php');

?>