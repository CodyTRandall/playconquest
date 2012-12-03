<?php

include('./header.php');

if(!isset($sid)){
	echo 'Error, you are not logged into a server';
	exit();
}
if(isset($_GET[gid])){
	$gid = escape_data($_GET[gid]);
}else{
	echo 'Error';
	exit();
}

//Start Javascript
echo '<Script language="Javascript">';

//Define the query for System information
$query = "SELECT id,name,x,y,ownerid,img FROM planets$sid WHERE z=$gid";
$result = mysql_query($query);
$rowNumber = mysql_num_rows($result);
$counter = 0;

//Define Javascript Array
//Id, name, x, y, owner
//define the system class
echo 'var systems = new Array();';
echo'
function system(id,name,img,x,y,owner,uname,ptype){
	this.x = x;
	this.y = y;
	this.id = id;
	this.name = name;
	this.owner = owner;
	this.ownername = uname;
	this.active = false;
	this.img = img;
	this.ptype = ptype;
}';


while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	//Create a system object for each item in db
	echo 'systems['.$counter.'] = new system('.$row[id].',"'.$row[name].'",'.$row[img].','.$row[x].','.$row[y].','.$row[ownerid].',"'.getUserStat(username,getPlanetStat(ownerid,$sid,$row[id])).'","'.getTypeStat(name,$row[img]).'");';
	$counter++;
}

//end js declaration

//end js
echo '</script>';

//Print Canvas
echo '<canvas id="c"></canvas><script src="planet.js"></script>';

//Print the action menu
echo '<table cellspacing=5 cellpadding=5 align=center>
	</table>';
	//Print the action menu
echo '<table cellspacing=5 cellpadding=5 align=center>
		<tr>
			<td><b>Name:</b></td>
			<td><div id="name"></div></td>
		</tr><tr>
			<td><b>Coords:</b></td>
			<td>(<span id="x">0</span>,<span id="y">0</span>)</td>
		</tr><tr>
			<td><b>Owner:</b></td>
			<td><div id="owner"></div></td>
		</tr><tr>
			<td><b>Type:</b></td>
			<td><div id="type"></div></td>
		</tr>
	</table>';
	
include('./footer.php');

?>