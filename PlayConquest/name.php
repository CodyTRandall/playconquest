<?php /**/ ?><?php

include('./header.php');

if(isset($_GET[id])){

	$pid = escape_data($_GET[id]);
	
	//Make sure you own
	if(getPlanetStat(ownerid,$sid,$pid) != $id){
		echo 'err';
		exit();
	}
}else{
	echo 'err';
	exit();
}

if(isset($_POST['name'])){

	$name = ''.escape_data($_POST['name']);
	
	setPlanetStat(name,$name,$sid,$pid);
	
	changePage('./planets.php');
	
}

echo '<form action="./name.php?id='.$pid.'" method="post">
		<input type="text" name="name" value="'.getPlanetStat(name,$sid,$pid).'"> <input type="Submit" value="Change">';
	
	
include('./footer.php');

?>