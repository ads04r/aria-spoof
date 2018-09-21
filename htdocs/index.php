<?php

include_once("functions.php");

$url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
if(strcmp($_SERVER['HTTP_HOST'], "www.fitbit.com") == 0) { $url = "http://104.16.65.50" . $_SERVER['REQUEST_URI']; }
$path = dirname(dirname(__FILE__)) . "/data/" . gmdate("YmdHis");
if(!(is_dir($path))) { mkdir($path); }

$headers = array();
foreach(getallheaders() as $k => $v)
{
	$headers[] = $k . ": " . $v;
}
$fp = fopen($path . "/request_headers.json", "w");
fwrite($fp, json_encode(array("call"=>$_SERVER,"headers"=>$headers), JSON_PRETTY_PRINT));
fclose($fp);

$postdata = file_get_contents("php://input");
$fp = fopen($path . "/request_data", "w");
fwrite($fp, $postdata);
fclose($fp);

$retheaders = array();

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

$fp = fopen($path . "/response_headers.json", "w");
fwrite($fp, json_encode($retheaders, JSON_PRETTY_PRINT));
fclose($fp);

$fp = fopen($path . "/response_data", "w");
fwrite($fp, $op);
fclose($fp);

foreach($retheaders as $header)
{
	if(preg_match("/:/", $header) == 0) { continue; }
	header($header);
}

print($op);
