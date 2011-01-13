<?php

define('PUN_ROOT', './');
require 'config.php';
require PUN_ROOT.'include/functions.php';
require PUN_ROOT.'include/utf8/utf8.php';
require PUN_ROOT.'include/dblayer/common_db.php';
require PUN_ROOT.'include/parser.php';

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

// Player message id
$pcmid = isset($_GET['pcmid']) ? intval($_GET['pcmid']) : 0;

// fetch message
$result = $db->query('SELECT nodeindex, nodetype, message FROM '.$db->prefix.'nodes WHERE nodetype IN (1,3) AND parentindex='.$pcmid) or error('Unable to fetch nodes info', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result))
{
	$npcmessage = $db->fetch_assoc($result);
	if ($npcmessage['nodetype'] == 3)
	{
		$array = split("[ \n]", $npcmessage['message']);
		$label = $array[1];
		$result = $db->query('SELECT nodeindex, message FROM '.$db->prefix.'nodes WHERE nodetype=1 AND label=\''.$label.'\'') or error('Unable to fetch nodes info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			$npcmessage = $db->fetch_assoc($result);
		}
		else
		{
			error('Unable to find label:'.$label);
		}
	}
	$npcmessage['message'] = parse_message($npcmessage['message'], '0');
	echo '<p>'.$npcmessage['message'].'</p>'."\n";

	// fetch choices
	$result = $db->query('SELECT id, nodeindex, nodetype, message FROM '.$db->prefix.'nodes WHERE parentindex='.$npcmessage['nodeindex']) or error('Unable to fetch nodes info', __FILE__, __LINE__, $db->error());

	if ($db->num_rows($result))
	{
		while ($cur_message = $db->fetch_assoc($result))
		{
			$cur_message['message'] = parse_message($cur_message['message'], '0');
			if ($cur_message['nodetype'] == 1)
			{
				echo '<p><a href="dialog.php?pcmid='.$npcmessage['nodeindex'].'">Next</a></p>'."\n";
			}
			else if ($cur_message['nodetype'] == 2)
			{
				echo '<p><a href="dialog.php?pcmid='.$cur_message['nodeindex'].'">'.$cur_message['message'].'</a></p>'."\n";
			}
		}
	}
}
else
{
	echo 'THE END';
}

$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_main>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <pun_main>

// Close the db connection (and free up any result data)
$db->close();

// Spit out the page
exit($tpl_main);
?>
