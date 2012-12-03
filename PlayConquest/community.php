<?php

include('./header.php');

//Print the menu  
echo '
<script language="Javascript">
var change = function(x){

   for(var y=1; y<2; y++){
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
<a href="javascript:;" onClick="change(1)">Voting</a> | 
<a href="./board/">Forum</a> | 
<a href="./wiki/">Wiki</a>
</center><hr><Br>';

if(!isset($id)){
  echo 'Error you must be logged in to vote!.';
}

echo '<div id="1">Invite Points '.invitePoints($id).'
  <table cellspacing=5>
  <tr>
    <td><b>Name</b></td>
    <td><b>Type</b></td>
    <td><b>Link</b></td>
    <td><b>IP Value</b></td>
  </tr><tr>
  <td>Player Invite</td>
  <td>Invite</td>
  <td>http://www.playconquest.com/register.php?key='.getUserStat(invitekey,$id).'</td>
  <td>1</td>
  </tr><tr>
  <td>BGL.com</td>
  <td>Vote</td>
  <td>
<a href="http://www.browsergamelist.com/vote.php?gid=105&id='.$id.'" target="_blank" id="'.$id.'"><img src="http://www.browsergamelist.com/media/browsergamelist-vote2.png" alt="Vote now at BrowserGameList" title="Vote now at BrowserGameList" /></a></td>
  <td>.1</td>
  </tr></table></div>';

include('./footer.php');
?>