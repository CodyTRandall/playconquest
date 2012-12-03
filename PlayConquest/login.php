<?php


require_once('./db_connect.php');
$h=0;
if(isset($_POST['submitted']))
$errors=array();

if(empty($_POST['email'])){
$errors[]='no email';
}else{
$e=escape_data($_POST['email']);
}

if(empty($_POST['password'])){
$errors[]='no pass';
}else{
$p=escape_data($_POST['password']);
}

if(empty($errors)){

$query="SELECT id FROM users WHERE email='$e' AND password=md5('$p')";
$result=@mysql_query($query);
$row=mysql_fetch_array($result,MYSQL_NUM);
if($row){
  $query="SELECT id FROM users WHERE id=$row[0]";
  if($row){
    session_start();
    $_SESSION['user_id']=$row[0];
    $theip = $_SERVER['REMOTE_ADDR'];
    $query = "UPDATE users SET lastip='$theip' WHERE id=$row[0]";
    $result = @mysql_query($query);
    echo mysql_error();
    include('./header.php');
    echo 'Logging in...';
                include('./footer.php');
    echo '<meta http-equiv="REFRESH" content="0;url=./server.php">';
    exit();
  }
}else{
        include('./header.php');
  echo 'Your combo didnt match up<br>';
  $h=1;
  }
}

if($h==0){
  include('./header.php');
}
if(isset($_SESSION['user_id'])){
  echo 'err';
  exit();
}
echo '
<table cellpadding=5 cellspacing=5>
<FORM ACTION="login.php" method="post">
<tr>
  <td><b>Email:</b></td>
  <td><INPUT TYPE="text" name="email" size="20" maxlength="50"></td>
</tr><tr>
  <td><b>Password:</b></td>
  <td><INPUT TYPE="password" name="password" size="20" maxlength="50"></td>
</tr><tr>
  <td><INPUT TYPE="submit" name="submit" value="Login"></td>
  <td><INPUT TYPE="hidden" name="submitted" value="TRUE"></td>
</FORM>
</table>
';
include('./footer.php');
?>