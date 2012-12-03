<?php /**/ ?><?php

include('./header.php');

if(isset($_SESSION['user_id'])){
  echo 'err';
  exit();
}

$errors = array();

if(isset($_POST['submitted'])){

if(empty($_POST['name'])){
  $errors[]='Please enter your username';
  }else{
  $name=escape_data($_POST['name']);
    $pattern = "#[a-zA-Z]{3,}#";
  if(!preg_match_all($pattern,$name,$matches)){
    $errors[]='Invalid username must be A-Z characters only and have at least 3 characters.';
  }
}
if(empty($_POST['email'])){
  $errors[]='Please enter your email';
  }else{
  $email = escape_data($_POST['email']);
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
    $errors[]='Your email address was invalid.';
  }
}

if(empty($_POST['pass1'])){
  $errors[]='Please enter your password';
  }else{
  $pass1=escape_data($_POST['pass1']);
}
if(empty($_POST['pass2'])){
  $errors[]='Please enter your password';
  }else{
  $pass2=escape_data($_POST['pass2']);
}
if(empty($_POST['key'])){
  $key = 'alphainvite';
  }else{
  $key = escape_data($_POST['key']);
}

if($pass2!=$pass1){
echo 'Your passwords did not match!';
include('./footer.php');
exit();
}
$query = "SELECT id,invites FROM users WHERE invitekey='$key'";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
if($row){
  $invites = $row[invites];
  $invites++;
  $theid = $row[id];
  $query2 = "UPDATE users SET invites=$invites WHERE id=$theid";
  $result2 = @mysql_query($query2);
}
//Check
$newpass=md5($pass1);
if(empty($errors)){
  $invkey = md5($name);
  $query = "INSERT INTO users(username,email,password,invitekey,invitedbykey) VALUES ('$name','$email','$newpass','$invkey','$key')";
  $result=@mysql_query($query);
    if($result){
            echo 'You have successfully registered! You may now <a href="./login.php">Login</a>.';
      include('./footer.php');
            exit();
      } else {
        echo 'Your username/email was taken!<br><br>';
                
      }
} else {
  foreach($errors as $msg){
  echo "- $msg<br>";
}
}

}

echo '<br>
<form action="register.php" method="post">
<TABLE cellspacing="0" cellpadding="5">
<tr>
  <td><b>Username*</b></td>
  <td><input type="text" name="name" size="20" maxlength="15" value="" ></td>
  <td>A-Z only, at least 3 characters.
</tr>
<tr>
  <td><b>Email*</b></td>
  <td><input type="text" name="email" size="20" maxlength="50" value="" ></td>
</tr>
<tr>
  <td><b>Password*</b></td>
  <td><input type="password" name="pass1" size="20" maxlength="30" value="" ></td>
</tr>
<tr>
  <td><b>Confirm Password*</b></td>
  <td><input type="password" name="pass2" size="20" maxlength="30" value="" ></td>
</tr>
<tr>
  <td><b>Invite Key</b></td>
  <td><input type="text" name="key" size="20" maxlength="30" value="" id="key"></td>
</tr>
<tr><td></td><td><INPUT TYPE="Submit" name="submit" value="Register"></td></tr></table>
<input TYPE="hidden" name="submitted" value="TRUE">
</form><font size=1>* is required</font>';
if(isset($_GET[key])){
  echo '<script language="javascript">
      document.getElementById("key").value="'.$_GET[key].'";
      </script>';
}
include('./footer.php');
?>