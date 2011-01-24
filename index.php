<?php

//TODO:query start_index from db by given name
//$gameid = isset($_GET['gameid']) ? intval($_GET['gameid']) : 0;
$start_index = 1;

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
<script type="text/javascript" src="js/jquery.cookies.2.2.0.min.js"></script>
<script type="text/javascript">
var params = {};
function setCookie(c_name,value,expiredays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate()+expiredays);
$.cookies.set(c_name, value, {expiresAt: exdate});
}

function getCookie(c_name)
{
return $.cookies.get(c_name);
}

function deleteCookie(c_name)
{
$.cookies.del(c_name);
}

function getMCNode(datastring)
{
$.ajax({
  url: "ajax.php",
  type: "POST",
  data: (datastring),
  cache: false,
  dataType: "json",
  success: function(msg){
    $("#results").empty();
    $("#results").append(msg.message.message);
    if (msg.choices){
      for (i=0;i<msg.choices.length;i++){
        $("#results").append('<p><a href="#" onclick="getMCNodeByParentIndex('+msg.choices[i].id+')">'+msg.choices[i].message+"</a></p>");
      }
    }
    if (msg.params){
      params = msg.params;
    }
    setCookie("testgame", msg.message.id, 365);
    setCookie("testgame_params", params, 365);
  }
});
}

function paramsToString(parray)
{
  var first = true;
  var paramstring = '{';
  for (key in parray) {
    if (first == false) {
      paramstring += ',';
    }
    first = false;
    paramstring += '"' + key + '": ' + '"' + parray[key] + '"';
  }
  paramstring += '}';
  return paramstring;
}

function getMCNodeByIndex(index)
{
  var datastring = '{"nodeindex": '+ index + (params ? (', "params": ' + paramsToString(params)) : '') + '}';//{'nodeindex': index, 'params': {'a': 'aa', 'b': 'bb'} };
  getMCNode(datastring);
}

function getMCNodeByParentIndex(parentindex)
{
  var datastring = '{"parentindex": '+ parentindex + (params ? (', "params": ' + paramsToString(params)) : '') + '}';
  getMCNode(datastring);
}

$(document).ready( function(){
var savedgame = getCookie("testgame");
params = getCookie("testgame_params");
if (savedgame == null || savedgame == ""){
getMCNodeByIndex(<?php echo $start_index ?>);
} else {
getMCNodeByIndex(savedgame);
}
} );
</script>

<div class="box" id="results"></div>
<div class="clearset"></div>
<div class="box">
	<a href="#" onclick="deleteCookie('testgame');deleteCookie('testgame_params');params={};getMCNodeByIndex(<?php echo $start_index ?>);">Restart game</a>
</div>

<?php
$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_main>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <pun_main>

// Spit out the page
exit($tpl_main);
?>
