<?php

include('./header.php');

//check if new fleet is being created
if(isset($_POST[submitted])){

	$pid = escape_data($_POST[pid]);
	$name = escape_data($_POST[name]);
	
	//make sure you own the planet
	if(getPlanetStat(ownerid,$sid,$pid) != $id){
		echo 'err';
		exit();
	}
	
	$query = "INSERT INTO fleet$sid(name,loc,ownerid) VALUES('$name',$pid,$id)";
	$result = @mysql_query($query);
	
	echo 'You have created the fleet.<br><br>';
}

//delete the empty fleet
if(isset($_GET[d])){

	$d = (integer) escape_data($_GET[d]);
	
	//Check if you own the fleet
	if(getFleetStat(ownerid,$sid,$d) != $id){
		echo 'err';
		exit();
	}
	
	$go=true;
	//Check if it is empty
	$query = "SELECT id FROM mastership";
	$result = mysql_query($query);
	$num = mysql_num_rows($result);
	for($x=1; $x<$num; $x++){
		$str = 'ship'.$x;
		if(getFleetStat($str,$sid,$d)!=0){
			$go=false;
			break;
		}
	}
	
	if($go){
		$query = "DELETE FROM fleet$sid WHERE id=$d";
		$result = @mysql_query($query);
		echo 'Your fleet has been deleted.<br><br>';
	}else{
		echo 'The fleet must be empty to delete it.<br><br>';
	}
}

//reset the probes
if(isset($_GET[p])){

	$p = (integer) escape_data($_GET[p]);
	
	//Check if you own the fleet
	if(getFleetStat(ownerid,$sid,$p) != $id){
		echo 'err';
		exit();
	}
	
	setFleetStat(probes,0,$sid,$p);
	setFleetStat(probetime,0,$sid,$p);
	setFleetStat(report," ",$id,$p);
}
/***********
JAVASCRIPT AND MENU
**********/
echo '
<script language="Javascript">
var change = function(x){

	 for(var y=0; y<3; y++){
		document.getElementById(y).style.display = "none";
	}
	if(document.getElementById(x).style.display == "block"){
		document.getElementById(x).style.display = "none";
	}else{
		document.getElementById(x).style.display = "block";
	}
}
</script>

<center>
<br>
<a href="javascript:;" onClick="change(0)">Overview</a> | 
<a href="javascript:;" onClick="change(1)">New Fleet</a> | 
<a href="javascript:;" onClick="change(2)">Probe Reports</a>
</center><br>';
/********
OVERVIEW DETAILS
************/
echo '<div id="0" style="display:block">
		<table cellspacing=10 align=center>
		<tr>
			<td><b>Name</b></td>
			<td><b>Loc</b></td>
			<td><b>Size</b></td>
			<td><b>Destination</b></td>
			<td><b>Arrival Time</b></td>
			<td><b>Details</b></td>
			<td><b>Delete</b></td>
		</tr>';

$query = "SELECT id,name,loc,isplanet,inport,fuel,destination,destinationtime,combatid FROM fleet$sid WHERE ownerid=$id";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
	echo'<tr>
			<td><a href="./fname.php?id='.$row[id].'">'.$row[name].'</a></td>';
	//Print the planet name if on a planet or traveling if traveling
	if($row[destination]>0){
		if(landFleet($sid,$row[id])){
			echo '<td><a href="./planetdetails.php?id='.$row[destination].'">'.getPlanetStat(name,$sid,$row[destination]).'</a></td>';
		}else{
				echo '<td>Traveling</td>';
		}
	}else{
		echo '<td><a href="./planetdetails.php?id='.$row[loc].'">'.getPlanetStat(name,$sid,$row[loc]).'</a></td>';
	}

	//Continue printing
	echo '<td>'.fleetSize($sid,$row[id]).'</td>';
	if(getFleetStat(destination,$sid,$row[id])>0){
		$timeleft = timeLeft($row[destinationtime]);
		echo '<td><a href="./planetdetails.php?id='.$row[destination].'">'.getPlanetStat(name,$sid,$row[destination]).'</a></td>';
		if($timeleft>0){
				echo'<td>'.$timeleft.'</td>';
		}else{
			echo '<td>In Combat</td>';
		}
	}else{
		echo '<td align="center">None</td>
				<td align="center">---</td>';
	}
	echo '<td><a href="./fleetdetails.php?id='.$row[id].'">Details</a></td>';
	echo '<td><a href="./fleet.php?d='.$row[id].'">X</a></td>';
}
echo '</table></div>';

/*************
CREATE FLEET
**************/
echo '<div id="1" style="display:none">
		<form name="fleetmake" action="./fleet.php" method="post">
		<table cellspacing=5 align="center">
			<tr>
				<td><b>Name</b></td>
				<td><input type="text" name="name"></td>
			</tr><tr>
				<td><b>Planet</b></td>
				<td><select name="pid">';

$query = "SELECT id,name FROM planets$sid WHERE ownerid=$id";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
	echo '<option value="'.$row[id].'">'.$row[name].'</option>';
}
echo '</td></tr><tr>
		<td></td><td><input type="submit" value="Create"></td><input type="hidden" value="true" name="submitted">
		</table></div>';

/***********
PROBE REPORTS
************/
$query = "SELECT name,report,id,probetime FROM fleet$sid WHERE ownerid=$id AND probes>0";
$result = mysql_query($query);
$num = mysql_num_rows($result);
echo '<div id="2" style="display:none">';
if($num>0){
	echo '<table cellspacing="10" align="center">
			<tr>
				<td><b>Name</b></td>
				<td><b>Time Left</b></td>
				<td><b>Reload</b></td>
				<td><b>Report</b></td>
			</tr>';
}
while($row = mysql_fetch_array($result)){
	$timeleft = timeLeft($row[probetime]);
	if($row[report]==""){
		if($timeleft>0){
			$name = "In Transit";
		}else{
			$name = "Processing";
		}
	}else{
		$name = '<a href="./report.php?id='.$row[id].'">Available</a>';
	}
	if($timeleft<0){
		$timeleft = "Probing";
	}
	echo '<tr>
			<td>'.$row[name].'</td>
			<td>'.$timeleft.'</td>
			<td><a href="./fleet.php?p='.$row[id].'">Reload Probes</a></td>
			<td>'.$name.'</td>
		</tr>';
}
if($num>0){
	echo '</table>';
}else{
	echo 'You have no probes out.';
}
echo '</div>';

include('./footer.php');

?>