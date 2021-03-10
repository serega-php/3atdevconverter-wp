<?php if(!defined('_MY')) exit('Goodbye!');

$response = $converter->cron();

echo str_replace(PHP_EOL, '<br />', $response);

$converter->profiler();

?>