<?php /**/ ?><?php

include('./header.php');

if(isset($_GET['id'])){
	$oid = (integer) escape_data($_GET['id']);

	if(isset($_GET['type'])){
		$type = (integer) escape_data($_GET['type']);
		if($type!=1 && $type!=2){
			$type=1;
		}
	}else{
		$type=1;
	}
	
}else{
	echo 'err';
	exit();
}

/***********
TYPES
1 = Building
2 = Ship
***********/

echo '<table align="center" cellspacing=10>';

if($type==1){
	echo '<tr>
			<td><b>Name</b></td>
			<td><b>Civs Cost</b></td>
			<td><b>Elinarium Cost</b></td>
			<td><b>Cylite Cost</b></td>
			<td><b>Plexi Cost</b></td>
			<td><b>Base Cost</b></td>
		</tr>
		<tr>
			<td align="center">'.getBuildStat(name,$oid).'</td>
			<td align="center">'.getBuildStat(civscost,$oid).'</td>
			<td align="center">'.getBuildStat(steelcost,$oid).'</td>
			<td align="center">'.getBuildStat(cylitecost,$oid).'</td>
			<td align="center">'.getBuildStat(plexicost,$oid).'</td>
			<td align="center">'.getBuildStat(basecost,$oid).'</td>
		</tr>
	</table><br><br>
		'.getBuildStat(description,$oid);
}
if($type==2){
	echo '<tr>
			<td><b>Name</b></td>
			<td><b>Civs Cost</b></td>
			<td><b>Elinarium Cost</b></td>
			<td><b>Cylite Cost</b></td>
			<td><b>Plexi Cost</b></td>
			<td><b>Health</b></td>
			<td><b>Attack</b></td>
			<td><b>Defence</b></td>
			<td><b>Cargo</b></td>
		</tr>
		<tr>
			<td>'.getShipStat(name,$oid).'</td>
			<td>'.getShipStat(civcost,$oid).'</td>
			<td>'.getShipStat(steelcost,$oid).'</td>
			<td>'.getShipStat(cylitecost,$oid).'</td>
			<td>'.getShipStat(plexicost,$oid).'</td>
			<td>'.getShipStat(hp,$oid).'</td>
			<td>'.getShipStat(attack,$oid).'</td>
			<td>'.getShipStat(defend,$oid).'</td>
			<td>'.getShipStat(cargo,$oid).'</td>
		</tr>
	</table><br><br>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.getShipStat(description,$oid);
}
include('./footer.php');
?>
	