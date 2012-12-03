<?php /**/ ?><?php

include('./header.php');

if(isset($_GET['id'])){
  
  $uid = (integer) escape_data($_GET['id']);
}else{
  echo 'err';
  exit();
}

//check if deleting a message
if(isset($_GET['d'])){
  $d = (integer) escape_data($_GET['d']);

  //check if own
  $query = "SELECT toid FROM mail WHERE id=$d";
  $result = @mysql_query($query);
  $row = mysql_fetch_array($result);
  
  if($row[0]!=$id){
    echo 'err';
    exit();
  }else{
    //lower counter
    $mailnum = getUserStat(msgcount,$id);
    $mailnum--;
    $query = "UPDATE users SET msgcount=$mailnum WHERE id=$id";
    $result = @mysql_query($query);
    
    //delete from db
    $query = "DELETE FROM mail WHERE id=$d";
    $result = @mysql_query($query);
  }
  
  echo 'Your message has been deleted.<br><br>';
}

//check if message is being sent
if(isset($_POST['send'])){

  $msg = escape_data($_POST[msg]);
  $to = escape_data($_POST[to]);
  $title = escape_data($_POST[title]);
  
  $query = "SELECT id FROM users WHERE username='$to'";

  $result = mysql_query($query);
  //If user exists
  if($row = mysql_fetch_array($result)){
    
    $toid = $row[0];
    
    //Check if they can recieve a message
    $msgcount = getUserStat(msgcount,$toid);
    $msgcount++;
    if($msgcount > getUserStat(maxmsg,$toid)){
      echo 'This users mailbox is full.';
    }else{
      $query = "UPDATE users SET msgcount=$msgcount WHERE id=$id";
      $result = @mysql_query($query);
      $query = "INSERT INTO mail(toid,fromid,msg,title) VALUES ($toid,$id,'$msg','$title')";
      $result = @mysql_query($query);
      echo 'Your message was sent.';
    }
  }else{
    echo 'This user does not exist.';
  }
  echo '<br><br>';
}

//changing password
if(isset($_POST['submit3'])){

  if($id != $uid){
    echo 'err';
  }
  
  //Make sure they are trying to update the password
  $oldpass = escape_data($_POST[oldpass]);
  $newpass1 = escape_data($_POST[newpass1]);
  $newpass2 = escape_data($_POST[newpass2]);
  if($oldpass!=''){
  //Make sure the old pass is correct
  if(md5($oldpass) != getUserStat(password,$id)){
    echo 'Your password did not match the one on file.<br><br>';
  }else{
    //Make sure the newpass matches
    if($newpass1 != $newpass2){
      echo 'Your passwords didnt not match.<br><br>';
    }else{
      //Update
      $pass = md5($newpass1);
      $query = "UPDATE users SET password='$pass' WHERE id=$id";
      $result = mysql_query($query);
    }
  }}
  
  $color = (integer) escape_data($_POST['color']);
  
  if($color>-1 && $color<13){
    $query = "UPDATE users SET color=$color WHERE id=$id";
    $result = @mysql_query($query);
  }
}
  
  
if(isset($_POST[submit])){

  //Make sure you own the profile
  if($id!=$uid){
    echo 'err';
    exit();
  }
  
  $bio = escape_data($_POST[bio]);
  
  $query = "UPDATE users SET bio='$bio' WHERE id=$id";
  $result = @mysql_query($query);
  
  echo 'You have updated your bio.<br><br>';
}

if(isset($_POST[submitt])){

  //Define
  echo 'a';
  function getExtension($str){
    $i = strrpos($str,".");
         if (!$i) { return ""; }
         $l = strlen($str) - $i;
         $ext = substr($str,$i+1,$l);
         return $ext;
  }
  
  $img = $_FILES['file']['name'];
  
  if($img){
    echo 'b';
    $filename = stripslashes($_FILES['file']['name']);
    $ext = getExtension($filename);
    
    if($ext != "jpg"){
      echo 'Your extension is not valid. Please make sure it is .jpg, not .JpG, not .png, not .JPG.<br><br>';
    }else{
      echo 'c';
      $size=filesize($_FILES['file']['tmp_name']);
      echo $size;
      if($size>5000*1024){
        echo 'You have exceeded the size limit.<br><br>';
      }else{
        
        $src = "profile/".$id.".jpg";
        
        $copy = copy($_FILES['file']['tmp_name'], ''.$src);
        if($copy){
          echo 'File upload was succesful<br><br>';
        }else{
          echo 'File upload was not successful<br><br>';
        }
        
      }
    }
  }else{
    echo 'There was an error processing your img<br><br>';
  }

}
  
  
//Print the user details
echo '<u>'.getUserStat(username,$uid).'</u><br><br>
<table cellspacing=5>
    <tr>
      <td>';
      
      if(file_exists("./profile/".$uid.".jpg")){
        echo '<img src="./profile/'.$uid.'.jpg" height="250" width="300">';
      }else{
        echo '<img src="./profile/def.png">';
      }
    echo'</td>
      <td>
          <table cellspacing=10 align="top">
            <tr>
              <td>Wins</td>
              <td>'.getUserStat(wins,$uid).'/'.getUserStat(games,$uid).'</td>
            </tr><tr>
              <td>Level</td>
              <td>'.getUserStat(level,$uid).'</td>
            </tr><tr>
              <td>Votes</td>
              <td>'.getUserStat(votes,$uid).'</td>
            </tr><tr>
              <td>Invites</td>
              <td>'.getUserStat(invites,$uid).'</td>
            </tr>
          </table>
      </td>
    </tr>
  </table>';

//Print the menu  
echo '
<script language="Javascript">
var change = function(x){

   for(var y=1; y<8; y++){
    document.getElementById(y).style.display = "none";
  }
  if(document.getElementById(x).style.display == "block"){
    document.getElementById(x).style.display = "none";
  }else{
    document.getElementById(x).style.display = "block";
  }
}
</script>

<center>
<br>
<a href="javascript:;" onClick="change(1)">Bio</a> | ';

if($uid == $id){
  echo '
<a href="javascript:;" onClick="change(2)">Send Mail</a> | 
<a href="javascript:;" onClick="change(7)">Inbox ['.getUserStat(msgcount,$id).']</a> | 
<a href="javascript:;" onClick="change(5)">Profile</a> | 
<a href="javascript:;" onClick="change(4)">Invite</a> | 
<a href="javascript:;" onClick="change(6)">Perks</a> | ';
}
echo'
<a href="javascript:;" onClick="change(3)">Achievments</a>
</center><hr><br>';

//Create the mail div if you own this
if($uid == $id){
  echo '<div id="2" style="display:none">
  <form action="./profile.php?id='.$uid.'" method="post">
  <table cellspacing=5>
    <tr>
      <td>To</td>
      <td><input type="text" name="to"></td>
    </tr><tr>
      <td>Title</td>
      <td><input type="text" name="title"></td>
    </tr><tr>
      <td>Msg</td>
      <td><textarea rows="17" cols="75" name="msg"></textarea>
    </tr><tr>
      <td></td>
      <td><input type="submit" value="Send" name="send"><form></td>
    </tr></table>
  </div>';
}else{
  echo '<div id="2"></div>';
}

/*********
INBOX
**********/
echo '<div id="7" style="display:none"><u>Inbox</u><br><br>';
if($uid == $id){

  $msgcount = getUserStat(msgcount,$id);
  if($msgcount>0){
  
  echo '<script language="Javascript">
    var change2 = function(x){
    if(document.getElementById(x+"m").style.display == "block"){
      document.getElementById(x+"m").style.display = "none";
    }else{
      document.getElementById(x+"m").style.display = "block";
    }
    }
    </script>

    <table cellspacing=10>
      <tr>
        <td><b>From</b></td>
        <td><b>Time</b></td>
        <td><b>Title</b></td>
        <td><b>Reply</b></td>
        <td><b>Delete</b></td>
      </tr>
';

  $query = "SELECT thetime,fromid,readcount,id,title,msg FROM mail WHERE toid=$id";
  $result = mysql_query($query);
  while($row = mysql_fetch_array($result)){
    echo '<span id="'.$row[id].'m" style="display:none">From: '.getUserStat(username,$row[fromid]).'<br>Title:'.$row[title].'<br>'.$row[msg].'<br><hr></span>
      <tr>
        <td>'.getUserStat(username,$row[fromid]).'</td>
        <td>'.date ("m/d H:i", $row[thetime]).'</td>
        <td><a href="javascript:;" onClick="change2('.$row[id].')">'.$row[title].'</td>
        <td><a href="./profile.php?id='.$id.'&r='.$row[fromid].'">Reply</a></td>
        <td><a href="./profile.php?id='.$id.'&d='.$row[id].'">X</a></td>
      </tr>';
  }
  echo '</table>';
  
  }else{
    echo 'Your inbox is empty';
  }
}
echo '</div>';

//Create the bio div
echo '<div id="1"><u>Bio</u><br><br>';
if($uid == $id){
  echo '<form action="./profile.php?id='.$uid.'" method="post">
      <textarea rows="10" cols="30" name="bio">'.getUserStat(bio,$uid).'</textarea><br>
      <input type="submit" value="Submit" name="submit"></form>';
}else{
  echo getUserStat(bio,$uid);
}
echo '</div>';

//Create the achiev
echo '<div id="3" style="display:none">
<u>Achievements</u><br><br>
<table cellspacing=5 align="center">
    <tr>
      <td><u><b>Name</b></u></td>
      <td><u><b>Description</b></u></td>
      <td><u><b>Points</b></u></td>
      <td><u><b>Date</b></u></td>
    </tr>';

$markers;
$counter = 0;
$query = "SELECT aid,date FROM achiev WHERE ownerid=$uid";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
  
  $query2 = "SELECT id,name,disc,points FROM masterachiev WHERE id=$row[aid]";
  $result2 = mysql_query($query2);
  $row2 = mysql_fetch_array($result2);
  $markers[$counter] = $row2[id];
  $counter++;
  
  echo '<tr>
      <td><b>'.$row2[name].'</b></td>
      <td><b>'.$row2[disc].'</b></td>
      <td><b>'.$row2[points].'</b></td>
      <td><b>'.$row[date].'</b></td>
    </tr>';
}

$query = "SELECT id,name,disc,points,visible FROM masterachiev";
$result = mysql_query($query);
$printed = false;
echo mysql_error();
while($row = mysql_fetch_array($result)){
  for($x = 0; $x<sizeof($markers); $x++){
    if($markers[$x] == $row[id]){
      $printed = true;
      break;
    }
  }
  if($row[visible] && !$printed){
    echo '<tr>
        <td>'.$row[name].'</td>
        <td>'.$row[disc].'</td>
        <td>'.$row[points].'</td>
        <td></td>
      </tr>';
  }
  $printed = false;
}

echo '</table></div>';

//Create the invite
if($uid==$id){
echo '<div id="4" style="display:none"><u>Invite Key</u><br><br>
    Your invite key is <b>'.getUserStat(invitekey,$id).'</b><br><br> URL: <b>http://www.playconquest.com/register.php?key='.getUserStat(invitekey,$id).'</b>
  </div>';
}else{
  echo '<div id="4"></div>';
}

//Create the image upload and profile edit.
echo '<div id="5" style="display:none"><u>Edit Profile</u><br><br>';
if($uid==$id){
  echo '<form action="./profile.php?id='.$uid.'" method="post"  enctype="multipart/form-data"><table cellspacing=5>
      <tr>
        <td></td>
        <td>Profile Picture [must be .jpg and < 5mb]</td>
        <td><input type="file" name="file"></td>
      </tr><tr>
        <td></td>
        <td></td>
        <td><input type="submit" name="submitt" value="Upload">
      </tr>
    </table>
    </form>
    <br><br>
    <form action="./profile.php?id='.$uid.'" method="post">
    <table cellspacing=5>
      <tr>
        <td>Old Password</td>
        <td><input type="password" name="oldpass"></td>
      </tr><tr>
        <td>New Password</td>
        <td><input type="password" name="newpass1"></td>
      </tr><tr>
        <td>Confirm New</td>
        <td><input type="password" name="newpass2"></td>
      </tr><tr>
        <td>Player Color</td>
        <td><select name="color">
            <option value="-1">No Change</option>
            <option value="0">Red</option>
            <option value="1">Blue</option>
            <option value="2">Green</option>
            <option value="3">Yellow</option>
            <option value="4">Orange</option>
            <option value="5">Purple</option>
            <option value="6">Lime</option>
            <option value="7">Pink</option>
            <option value="8">Light Grey</option>
            <option value="9">Magenta</option>
            <option value="10">Yellow Green</option>
            <option value="11">Teal</option>
          </select></td>
      </tr><tr>
        <td></td>
        <td><input type="submit" name="submit3" value="Submit"></td>
      </tr></table></form>
            ';
}
echo '</div>';
/********
PERKS PAGE
*********/
echo '<div id="6" style="display:none">';
$query = "SELECT id FROM masterperks";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){

}

//Change the page to send mail if reply is set
if(isset($_GET[r])){
  $r = escape_data($_GET[r]);
  $query = "SELECT username FROM users WHERE id=$r";
  $result = mysql_query($query);
  if($row = mysql_fetch_array($result)){
    $r = $row[0];
  }else{
    $r = "User Not Found";
  }
  echo '<script>change(2);
    document.forms[0].to.value="'.$r.'"</script>';
}
if(isset($_GET['d'])){
  echo '<script>change(7)</script>';
}
echo '</div>';
include('./footer.php');

?>