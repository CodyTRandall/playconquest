<?php /**/ ?><?php
echo '<TITLE>Conquest - Free Online Browser Based Space Strategic Warfare Game | Free to play forever.</TITLE>';
session_start();
require_once('./db_connect.php');

//The user is logged in
if(isset($_SESSION['user_id'])){

  //Pull out the User's top level variables.
  $id=$_SESSION['user_id'];
  
  //Check if downtime is up
  $query = "SELECT downtime,adminid FROM admin";
  $result = mysql_query($query);
  $row = mysql_fetch_array($result);
  echo mysql_error();
  if($row[0]>0 && $row[1]!=$id){
    echo 'I\'m sorry to inform you that our servers are currently being patched! Please check back later';
    exit();
  }
  
  //Echo CSS
  echo'
  <head>
  <link rel="shortcut icon" href="favicon.ico" />
  <link rel="stylesheet" type="text/css" href="./style.css" />
  </head>
  <body>
  <div id="container">
  <div id="header">
  <img src="./images/logo.png" width="874" height="117" alt="Conquest" />
  </div>
  <div id="parameter">
  <table align="center" id="main_parameter" width="100%" cellpadding="0" cellspacing="0" style="position: relative;">
  <tr>
  <td id="tl"></td>
  <td id="tm"></td>
  <td id="tr"></td>
  </tr>
  <tr>
  <td id="left">
  </td>';

  //Print css for left menu
  echo'<td id="content_parameter">
    <div id="main">
    <div id="menu">
      <ul>
        <li id="nav_first"><a href="./news.php">News</a></li>
        <li><a href="./profile.php?id='.$id.'">Profile</a></li>';
  //Check to see if the user is logged into a server
  if(isset($_SESSION['sid'])){
  
    //Define the server id
    $sid=$_SESSION['sid'];
  
    //Echo Top Logged-In Menu for inside a server
    echo'
        <li><a href="./notifications.php">Notification ['.notificationCount($sid,$id).']</a></li>
        <li><a href="./planets.php">Planets</a></li>
        <li><a href="./fleet.php">Fleets</a></li>
        <li><a href="./research.php">Research</a></li>
        <li><a href="./galaxy.php">Galaxy</a></li>
        <li><a href="./ranking.php">Ranking</a></li>
    <li><a href="./community.php">Community</a></li>';

  }else{
    //Echo Top Logged-In Menu for outside a server
    echo'<li><a href="./server.php">Servers</a></li>';

  }
  
  //Print the end to the top left menu
    echo ' 
        <li><a href="./logout.php">Logout</a></li>';
    
  //Print the players menu
  echo '</ul><div id="player_stats">
    <center>
      <script type="text/javascript"><!--
      google_ad_client = "ca-pub-0268509440976904";

      google_ad_slot = "2405895183";
      google_ad_width = 125;
      google_ad_height = 125;
      //-->
    </script>
    <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
    </script>
    <script type="text/javascript"><!--
    google_ad_client = "ca-pub-0268509440976904";
    /* ConquestSide */
    google_ad_slot = "2405895183";
    google_ad_width = 125;
    google_ad_height = 125;
    //-->
    </script>
    <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script></center></div><br></div>';
}else{

  //Echo HTML Layout
  echo'
  <head>
  <link rel="shortcut icon" href="favicon.ico" />
  <link rel="stylesheet" type="text/css" href="./style.css" />
  </head>
  <body>
  <div id="container">
  <div id="header">
  <img src="./images/logo.png" width="874" height="117" alt="Conquest" />
  </div>
  <div id="parameter">
  <table align="center" id="main_parameter" width="100%" cellpadding="0" cellspacing="0" style="position: relative;">
  <tr>
  <td id="tl"></td>
  <td id="tm"></td>
  <td id="tr"></td>
  </tr>
  <tr>
  <td id="left">
  </td>
  ';

  //Echo logged out menu
  echo'<td id="content_parameter">
  <div id="main">
  <div id="menu">
    <ul>
      <li id="nav_first"><a href="./index.php">Home</a></li>
      <li><a href="./news.php">News</a></li>
      <li><a href="./login.php">Login</a></li>
      <li><a href="./register.php">Register</a> </li>
      <li><a href="./wiki/">Help</a></li>
  ';
  
    //Print the players menu
  echo '</ul><div id="player_stats">
    <center>
      <script type="text/javascript"><!--
      google_ad_client = "ca-pub-0268509440976904";

      google_ad_slot = "2405895183";
      google_ad_width = 125;
      google_ad_height = 125;
      //-->
    </script>
    <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
    </script>
    <script type="text/javascript"><!--
    google_ad_client = "ca-pub-0268509440976904";
    /* ConquestSide */
    google_ad_slot = "2405895183";
    google_ad_width = 125;
    google_ad_height = 125;
    //-->
    </script>
    <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script></center></div><br></div>';
}

echo '<div id="Content">';

?>