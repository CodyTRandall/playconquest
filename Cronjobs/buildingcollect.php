<?php

require_once('./www.playconquest.com/db_connect.php');

$servercount = getServerNumber();
for($x=1; $x<$servercount+1; $x++){
	$query = "SELECT id FROM planets$x WHERE ownerid>0";
	$result = @mysql_query($query);

	while($row = mysql_fetch_array($result)){
	
		$pid = $row[id];
		$sid = $x;
		$id = getPlanetStat(ownerid,$sid,$pid);
		
		//Check if something is actually building
		$cid = getPlanetStat(constructionid,$sid,$pid);
	
		if($cid>0 && $cid!=8){
		
			//Check if it is finished
			if(timeLeft(getPlanetStat(constructiontime,$sid,$pid))<1){
				if(getBuildStat(increases,$cid) != "none"){
					$pstat = getPlanetStat(getBuildStat(increases,$cid),$sid,$pid);
					$increase = getBuildStat(increaseamt,$cid);
					$pstat = $pstat+$increase;
			
					if(setPlanetStat(getBuildStat(increases,$cid),$pstat,$sid,$pid)){
						if(setPlanetStat(constructionid,0,$sid,$pid)){
							addNotification(getBuildStat(name,$cid).' has finished building.',$sid,$id);
						}
					}
					
				}else{
					if(setPlanetStat(constructionid,0,$sid,$pid)){
						if(!galaxyControlled($sid,$pid)){
							$z = getPlanetStat(z,$sid,$pid);
							$query = "UPDATE galaxy$sid SET ownerpid=$pid WHERE id=$z";
							$result = @mysql_query($query);
							addNotification(getBuildStat(name,$cid).' has finished building. You now have control over this system.',$sid,$id);
						}else{
							addNotification(getBuildStat(name,$cid).' has finished building. This action has failed. Someone has gained control of the system before you. You must destroy the enemy '.getBuildStat(name,$cid).' or conquer the planet.',$sid,$id);
						}
					}
				}
			}
		}
	}
}
?>