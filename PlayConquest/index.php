<?php /**/ ?><?php

include('./header.php');

echo '
<script language="Javascript">
var change = function(x){

   for(var y=0; y<4; y++){
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
<div id="0" style="display:block;height:500px;">
<img src="./home/pic1.jpg">
</div>
<div id="1" style="display:none;height:500px;">
<img src="./home/pic2.jpg">
</div>
<div id="2" style="display:none;height:500px;">
<img src="./home/pic3.jpg">
</div>
<div id="3" style="display:none;height:500px;">
<img src="./home/pic4.jpg">
</div>
<br>
<a href="javascript:;" onClick="change(0)"><img src="./home/pic1.jpg" height=100 width=125></a>
<a href="javascript:;" onClick="change(1)"><img src="./home/pic2.jpg" height=100 width=125></a>
<a href="javascript:;" onClick="change(2)"><img src="./home/pic3.jpg" height=100 width=125></a>
<a href="javascript:;" onClick="change(3)"><img src="./home/pic4.jpg" height=100 width=125></a>
</center><br>';

echo 'Conquest - A free to play strategy based war game! Play as a new colonist out in an unknown Galaxy and fight your way through other players on your quest to rule the Universe. You start with one planet, then with that planet you build buildings to increase your productivity, eventually building massive fleets and using them to crush your enemies! Play today, always free.';

include('./footer.php');

?>
