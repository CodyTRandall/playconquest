<?php

require_once('./www.playconquest.com/db_connect.php');

$servercount = getServerNumber();
for($x=1; $x<$servercount+1; $x++){
	$query = "SELECT id FROM planets$x WHERE ownerid>0";
	$result = @mysql_query($query);

	while($row = mysql_fetch_array($result)){
		$pid = $row[0];
		$taxesPaid = true;
		$upkeep = getUpkeepCost($x,$pid);
		//Check to see if the upkeep can be paid
		$statsArray = array(1=>"steel", 0=>"cylite", 2=>"plexi");
		
		if($taxesPaid){
			//Pay the upkeep
			for($y=0; $y<count($statsArray); $y++){
				$current = getPlanetStat($statsArray[$y],$x,$pid);
				$current = $current-getUpkeepCost($x,$pid);
				setPlanetStat($statsArray[$y],$current,$x,$pid);
			}
			echo 'Taxes paid for '.$pid.'<br>';
			
			//Add civs
			$current = getPlanetStat(civs,$x,$pid);
			$current = $current + civProductionRate($x,$pid);
			echo 'New civs '.$current;
			if($current > getPlanetStat(maxcivs,$x,$pid)){
				$current = getPlanetStat(maxcivs,$x,$pid);
			}
			echo ' set to '.$current.'<br>';
			setPlanetStat(civs,$current,$x,$pid);
			
			//Add each resourceproductionrate to each resource
			for($y=0; $y<count($statsArray); $y++){
				$current = getPlanetStat($statsArray[$y],$x,$pid);
				$current = $current+resourceProductionRate($statsArray[$y],$x,$pid);
				echo resourceProductionRate($statsArray[$y],$x,$pid);
				setPlanetStat($statsArray[$y],$current,$x,$pid);
				echo $current.' is new resource<br>';
			}
		}
	}
}
?>