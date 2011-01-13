<?php

define('PUN_ROOT', './');
require 'config.php';
require PUN_ROOT.'include/functions.php';
require PUN_ROOT.'include/utf8/utf8.php';
require PUN_ROOT.'include/dblayer/common_db.php';
require PUN_ROOT.'include/parser.php';

// Player message id
$parentindex = isset($_GET['parentindex']) ? intval($_GET['parentindex']) : 0;


// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compatibility

// Send the Content-type header in case the web server is setup to send something else
header('Content-type: application/json; charset=utf-8');

$value = array();

$result = null;
// fetch message
if (isset($_GET['nodeindex']))
{
	$nodeindex = isset($_GET['nodeindex']) ? intval($_GET['nodeindex']) : 0;
	$result = $db->query('SELECT nodeindex, nodetype, message FROM '.$db->prefix.'nodes WHERE nodetype=1 AND nodeindex='.$nodeindex) or error('Unable to fetch nodes info', __FILE__, __LINE__, $db->error());
}
else
{
	$result = $db->query('SELECT nodeindex, nodetype, message FROM '.$db->prefix.'nodes WHERE nodetype IN (1,3) AND parentindex='.$parentindex) or error('Unable to fetch nodes info', __FILE__, __LINE__, $db->error());
}

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
	$value['message'] = array('id'=>$npcmessage['nodeindex'], 'message'=>$npcmessage['message']);

	// fetch choices
	$result = $db->query('SELECT id, nodeindex, nodetype, message FROM '.$db->prefix.'nodes WHERE parentindex='.$npcmessage['nodeindex']) or error('Unable to fetch nodes info', __FILE__, __LINE__, $db->error());

	if ($db->num_rows($result))
	{
		$choices = array();
		while ($cur_message = $db->fetch_assoc($result))
		{
			$cur_message['message'] = parse_message($cur_message['message'], '0');
			if ($cur_message['nodetype'] == 1)
			{
				$choices[] = array('id'=>$npcmessage['nodeindex'], 'message'=>'Next');
			}
			else if ($cur_message['nodetype'] == 2)
			{
				$choices[] = array('id'=>$cur_message['nodeindex'], 'message'=>$cur_message['message']);
			}
		}
		$value['choices'] = $choices;
	}
}
else
{
	$value['message'] = 'THE END';
}

echo json_encode($value);

$db->close();

