<?php

$start_id = isset($_GET['startid']) ? intval($_GET['startid']) : 0;

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compatibility

// Send the Content-type header in case the web server is setup to send something else
header('Content-type: text/html; charset=utf-8');

// Load the template
$tpl_file = 'main.tpl';
$tpl_main = file_get_contents($tpl_file);

// START SUBST - <pun_include "*">
preg_match_all('#<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">#', $tpl_main, $pun_includes, PREG_SET_ORDER);

foreach ($pun_includes as $cur_include)
{
	ob_start();

	// Allow for overriding user includes, too.
	if (file_exists($tpl_inc_dir.$cur_include[1].'.'.$cur_include[2]))
		require $tpl_inc_dir.$cur_include[1].'.'.$cur_include[2];
	else if (file_exists(PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2]))
		require PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2];
	else
		error(sprintf($lang_common['Pun include error'], htmlspecialchars($cur_include[0]), basename($tpl_file)));

	$tpl_temp = ob_get_contents();
	$tpl_main = str_replace($cur_include[0], $tpl_temp, $tpl_main);
	ob_end_clean();
}
// END SUBST - <pun_include "*">


// START SUBST - <pun_main>
ob_start();

?>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
function setCookie(c_name,value,expiredays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate()+expiredays);
document.cookie=c_name+ "=" +escape(value)+
((expiredays==null) ? "" : ";expires="+exdate.toUTCString());
}

function getCookie(c_name)
{
if (document.cookie.length>0)
  {
  c_start=document.cookie.indexOf(c_name + "=");
  if (c_start!=-1)
    {
    c_start=c_start + c_name.length+1;
    c_end=document.cookie.indexOf(";",c_start);
    if (c_end==-1) c_end=document.cookie.length;
    return unescape(document.cookie.substring(c_start,c_end));
    }
  }
return "";
}

function deleteCookie(c_name)
{
document.cookie=c_name+ "=;" +("expires=Thu, 01-Jan-1970 00:00:01 GMT");
}

function getMCNode(id)
{
$.ajax({
  url: "ajax.php",
  data: ({'pcmid': id}),
  cache: false,
  dataType: "json",
  success: function(msg){
    $("#results").empty();
    $("#results").append(msg.message);
    if (msg.choices){
      for (i=0;i<msg.choices.length;i++){
        $("#results").append('<a href="#" onclick="getMCNode('+msg.choices[i].id+')">'+msg.choices[i].message+"</a>");
      }
    }
    setCookie("testgame", id, 365);
  }
});
}

$(document).ready( function(){
var savedgame = getCookie("testgame");
if (savedgame == null || savedgame == ""){
getMCNode(<?php echo $start_id ?>);
} else {
getMCNode(savedgame);
}
} );
</script>

<div class="box" id="results"></div>
<div class="clearset"></div>
<div class="box">
	<a href="#" onclick="deleteCookie('testgame');getMCNode(<?php echo $start_id ?>);">Restart game</a>
</div>

<?php
$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_main>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <pun_main>

// Spit out the page
exit($tpl_main);
?>