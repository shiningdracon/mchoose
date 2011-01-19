<?php

function mc_script_parser($str, &$params)
{
	$goto = null;
	$lines = split("[\r\n]", $str);
	foreach ($lines as $line)
	{
		$array = preg_split("/[ \t]/", $line, -1, PREG_SPLIT_NO_EMPTY);
		if (count($array) == 0)
			continue;
		switch ($array[0])
		{
			case '#goto':
				$goto = parse_goto($array, $params);
				break;
			case '#define':
				parse_define($array, $params);
				break;
			case '#set':
				parse_set($array, $params);
				break;
			default:
				throw new Exception('error: unknow command.');
		}
	}
	return $goto;
}

function parse_define($array, &$params)
{
	if (count($array) != 2)
		throw new Exception('error: define format error.');
	if (array_key_exists($array[1], $params))
		throw new Exception('error: redefined variable.');
	$params[$array[1]] = 0;
	//return array('define', 'ok');
}

function parse_goto($array, &$params)
{
	if (count($array) != 2 && count($array) != 5)
		throw new Exception('error: goto format error.');
	$label = $array[1];
	if ($label == null || $label == '')
		throw new Exception('error: goto empty label.');
	if (count($array) == 2)
	{
		return array('goto', $label);
	}
	else
	{
		$variable_A = $params[$array[2]];
		$variable_B = $array[4];
		$operator = $array[3];

		$is_A_numeric = is_numeric($variable_A);
		$is_B_numeric = is_numeric($variable_B);
		if ($is_A_numeric != $is_B_numeric)
			throw new Exception('error: can not compare between to different type.');
		if ($is_A_numeric)
		{
			switch ($operator)
			{
			case '>':
				if (intval($variable_A) > intval($variable_B))
					return array('goto', $label);
				break;
			case '<':
				if (intval($variable_A) < intval($variable_B))
					return array('goto', $label);
				break;
			case '=':
				if (intval($variable_A) == intval($variable_B))
					return array('goto', $label);
				break;
			default:
				throw new Exception('error: unknow operator.');
			}
		}
		else
		{
			switch ($operator)
			{
			case '=':
				if ($variable_A == $variable_B)
					return array('goto', $label);
				break;
			default:
				throw new Exception('error: unknow operator.');
			}
		}
		return null;
	}
}

function parse_set($array, &$params)
{
	if (count($array) != 3 && count($array) != 4)
		throw new Exception('error: set format error.');
	if ($array[1] == null || $array[1] == '' || !array_key_exists($array[1], $params))
		throw new Exception('error: set a undefind variable:'.$array[1]);
	if (count($array) == 3) 
	{
		$params[$array[1]] = $array[2];
	}
	else
	{
		if (!is_numeric($array[3]) || !is_numeric($params[$array[1]]))
			throw new Exception('error: can not use arithmetic on a string.');

		switch ($array[2])
		{
		case '+':
			$params[$array[1]] = intval($params[$array[1]]) + intval($array[3]);
			break;
		case '-':
			$params[$array[1]] = intval($params[$array[1]]) - intval($array[3]);
			break; case '*':
			$params[$array[1]] = intval($params[$array[1]]) * intval($array[3]);
			break;
		case '/':
			$params[$array[1]] = intval($params[$array[1]]) / intval($array[3]);
			break;
		default:
			throw new Exception('error: not a valid arithmetic:'.$array[2]);
		}
	}
	//return array('set', 'ok');
}

/*
//debug
$param = array();

print("before:");
print_r($param);

$ret = mc_script_parser('
#goto asdfga
#define mya   
#set mya 1
#define myb   
#set myb + 2
#set mya hehehe
#goto helabel myb < 3

', $param);

print("ret:");
print_r($ret);
print("after:");
print_r($param);
*/

?>
