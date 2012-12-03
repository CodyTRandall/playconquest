<?php /**/ ?><?php

include('./header.php');

function updateCount($count,$id){
	$count++;
	if($count>3){
		$query = "DELETE FROM notifications WHERE id=$id";
		$result = @mysql_query($query);
		return 1;
	}
	$query = "UPDATE notifications SET readcount=$count WHERE id=$id";
	$result = @mysql_query($query);
	return 0;
}
echo '<table cellspacing="5">';

$query = "SELECT id,thetext,thedate,readcount FROM notifications WHERE ownerid=$id AND sid=$sid ORDER BY thedate DESC";
$result = @mysql_query($query);
if(mysql_num_rows($result)>0){
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		echo '<tr>
				<td><b>'.$row[thedate].'</b></td>
				<td>'.$row[thetext].'</td>
				</tr>';
				updateCount($row[readcount],$row[id]);
	}
}else{
	echo 'You have no notifications';
}

echo '</table>';

include('./footer.php');
?>