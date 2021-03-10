<?php 

/*
defines
*/

define('_MY', '...');

/*
folder for converter data
*/

$path = dirname(__FILE__).'/converter.data/';

if(!is_dir($path))
{
	mkdir($path, 0755);
	
	if(!is_dir($path))
	{
		die('dir problem '.$path);
	}
}

/*
converter.class
*/

require_once('converter.class/converter.class.php');

$converter = new Converter(array('basepath' => $path));

if(!$converter->ready)
{
	die('converter.class problem');
}

//customize
$converter->historylimit = 5;
$converter->cronmaxiteration = 3;
$converter->crondelaygrabsec = 5 * 60;

/*
logic
*/

if(isset($_GET['ajax']))
{
	require_once('ajax.php');
}
else if(isset($_GET['cron']))
{
	require_once('cron.php');
}
else if(isset($_GET['profiler']))
{
	require_once('profiler.php');
}

?>