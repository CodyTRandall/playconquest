<?php /**/ ?>
include('./db_connect.php');

$query = 
$filename = './names.txt';
$fd = fopen($filename,"r");
$contents = fread ($fd,filesize($filename));

fclose($fd);
$delimiter = ",";
$splitcontents = explode($delimiter,$contents);

$query = "SELECT id FROM galaxy1";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
	$id = $row[0];
	$name = $splitcontents[rand(0,sizeOf($splitcontents))-1];

	$query2 = "UPDATE galaxy1 SET name='$name' WHERE id=$id";
	$result2 = @mysql_query($query2);

	$query2 = "SELECT id FROM planets1 WHERE z=$id";
	$result2 = mysql_query($query2);
	$count = 0;
	while($row2 = mysql_fetch_array($result2)){
		$count++;
		if($count == 1){
			$str = "I";
		}
		if($count == 2){
			$str = "II";
		}
		if($count == 3){
			$str = "III";
		}
		if($count == 4){
			$str = "IV";
		}
		if($count == 5){
			$str = "V";
		}
		if($count == 6){
			$str = "VI";
		}
		echo $name.' '.$str.' '.$row2[0].'<br>';
		$str = $name.' '.$str;
		$pid = $row2[0];
		$query3 = "UPDATE planets1 SET name='$str' WHERE id=$pid";
		$result3 = mysql_query($query3);

	
	}

}


