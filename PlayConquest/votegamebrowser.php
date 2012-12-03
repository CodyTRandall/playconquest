<?php /**/ ?><?php

include('./db_connect.php');


if(isset($_GET[id])){
  $id = (integer) escape_data($_GET[id]);
  $vote = getUserStat(votes,$id);
  $vote++;
  $query = "UPDATE users SET votes=$vote WHERE id=$id";
  $result = @mysql_query($query);
}
?>