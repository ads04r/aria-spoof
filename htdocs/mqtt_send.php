#!/usr/bin/php -q
<?php

include_once("functions.php");

define('MQTT_BROKER', '127.0.0.1');
define('MQTT_PORT', 1883);
define('MQTT_CLIENT_ID', "pubclient_" + getmypid());
define('MQTT_STATE_TOPIC', "FitBit/Aria/State");
define('MQTT_SAMPLES_TOPIC', "FitBit/Aria");

if(count($argv) < 2)
{
	error_log("Call with a request_data file as an argument.");
	exit(1);
}

if (extension_loaded('mosquitto'))
{
	$client = new Mosquitto\Client(MQTT_CLIENT_ID);
	$client->connect(MQTT_BROKER, MQTT_PORT, 60);
	$postdata = file_get_contents($argv[1]);
	$ret = interpret_data($postdata);
	$state = array();
	$state['battery'] = $ret['battery'];
	$state['protocol'] = $ret['protocol'];
	$state['firmware'] = $ret['firmware'];
	$state['mac'] = $ret['mac'];
	$message = json_encode($state);
	$client->publish(MQTT_STATE_TOPIC, $message, 0, false);
	$client->loop();
	foreach($ret['readings'] as $item)
	{
		$message = json_encode($item);
		$client->publish(MQTT_SAMPLES_TOPIC, $message, 0, false);
		$client->loop();
	}
}else
{
	error_log("Mosquitto extension not loaded!");
}
