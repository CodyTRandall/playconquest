<?php

//Define basic session and database variables
session_start();
require_once('./db_connect.php');

//Start Javascript echos
echo '<script language="JavaScript">';

//Define the query for planet information
$query = "SELECT name, x, y, img, id FROM planets WHERE z=0";
$result = @mysql_query($query);
//Define the rows that we selected and create a counter
$rowNumber = mysql_num_rows($result);
$counter=0;

//Define the JavaScript Array
/*******Array construction
Index	Name	x	y	img	id
0		Planet1	20	50	3	15
[0]		[0][0]	[0][1]... etc
********/
echo 'var planets = new Array();';

//Loop for the number of rows
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){

	//First we define a 2nd dimension array for the given planet index, then we set the variables
	echo '
			planets['.$counter.'] = new Array(5);
			planets['.$counter.'][0] = "'.$row[name].'";
			planets['.$counter.'][1] = '.$row[x].';
			planets['.$counter.'][2] = '.$row[y].';
			planets['.$counter.'][3] = '.$row[img].';
			planets['.$counter.'][4] = '.$row[id].';
			';
			$counter++;
}

//End Javascript
echo '</script>';



//Echo the body including our canvas element
echo '
<head>
<title>Testing HTML 5 Canvas</title>
<style>
  body {
    margin:0px;
    padding:0px;
    text-align:center;
  }

  canvas{
    outline:0;
    border:1px solid #000;
    margin-left: auto;
    margin-right: auto;
  }
</style>
</head>

<canvas id="c"></canvas>
<script src="game.js"></script>
';
?>