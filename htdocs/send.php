#!/usr/bin/php -q
<?php

include_once("functions.php");

$url = "http://104.16.65.50/scale/upload"; 
$path = "bluh";
if(count($argv) >= 2)
{
	$path = $argv[1];
}
if(!(file_exists($path)))
{
	error_log("Please use the path of a request file as an argument");
}

$postdata = file_get_contents($path);
$headers = array("Content-Length: " . strlen($postdata),"Connection: keep-alive","Content-Type: application\/x-www-form-urlencoded","Host: www.fitbit.com");
$retheaders = array();

// Alter the timestamp
$l = strlen($postdata);
$postdata = substr($postdata, 0, 38) . get_timestamp() . substr($postdata, 42);
if(strlen($postdata) != $l) { error_log("Unit test failed"); exit(1); }

function callback($curl, $header_line)
{
	global $retheaders;
	$retheaders[] = $header_line;
	return(strlen($header_line));
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADERFUNCTION, "callback");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
if(strlen($postdata) > 0)
{
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, spoof_data($postdata));
}
$op = curl_exec($ch);
curl_close($ch);

print($op);



