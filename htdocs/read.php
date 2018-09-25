#!/usr/bin/php -q
<?php

include_once("functions.php");

if(count($argv) < 2)
{
	error_log("Call with a request_data file as an argument.");
	exit(1);
}

$data = file_get_contents($argv[1]);
$ret = interpret_data($data);
print(json_encode($ret, JSON_PRETTY_PRINT));


