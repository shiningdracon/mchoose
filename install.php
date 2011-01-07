<?php
define('PUN_ROOT', './');
require 'config.php';
require PUN_ROOT.'include/dblayer/common_db.php';
require PUN_ROOT.'include/functions.php';
require PUN_ROOT.'include/utf8/utf8.php';

if (true)
{
	// Start a transaction
	$db->start_transaction();


	$schema = array(
		'FIELDS'		=> array(
			'id'			=> array(
				'datatype'		=> 'SERIAL',
				'allow_null'	=> false
			),
			'nodeindex'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'nodetype'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'parentindex'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'label' 		=> array(
				'datatype'		=> 'VARCHAR(255)',
				'allow_null'	=> false,
                'default'       => '\'\''
			),
			'message'		=> array(
				'datatype'		=> 'TEXT',
				'allow_null'	=> false
			),
		),
		'PRIMARY KEY'	=> array('id')
	);

	$db->create_table('nodes', $schema) or error('Unable to create nodes table', __FILE__, __LINE__, $db->error());


	$db->end_transaction();
}

