<?php /**/ ?><?php

session_start();

if(!($_SESSION['user_id'])){
	$url='http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
	if((substr($url,-1) == '/') OR (substr($url,-1) == '\\')){
		$url=substr($url,0,-1);
	}
	$url.='/index.php';
	exit();
}else{
	$_SESSION=array();
	session_destroy();
	setcookie('PHPSESSID','',time()-300,'/','',0);
}
include('./header.php');

echo "You are now logged out.";
include('./footer.php');
?>