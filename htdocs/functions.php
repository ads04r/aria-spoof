<?php

function interpret_data($postdata, $ignore_registered_users=true)
{
	$header = substr($postdata, 0, 46);
	$buffer = substr($postdata, 46);
	$itemstrings = array();
	while(strlen($buffer) >= 32)
	{
		$itemstrings[] = substr($buffer, 0, 32);
		$buffer = substr($buffer, 32);
	}
	$checksum = $buffer;

	$ret = array();
	$ret['date'] = unpack("V", substr($header, 38, 4))[1];
	$ret['friendly_date'] = gmdate("l jS F Y, g:ia", $ret['date']) . " UTC";
	$ret['item_count'] = unpack("V", substr($header, 42, 4))[1];
	$ret['battery'] = unpack("V", substr($header, 4, 4))[1];
	$ret['protocol'] = unpack("V", substr($header, 0, 4))[1];
	$ret['firmware'] = unpack("V", substr($header, 30, 4))[1];
	$ret['mac'] = dechex(unpack("C", substr($header, 8, 1))[1]) . ":" . dechex(unpack("C", substr($header, 9, 1))[1]) . ":" . dechex(unpack("C", substr($header, 10, 1))[1]) . ":" . dechex(unpack("C", substr($header, 11, 1))[1]) . ":" . dechex(unpack("C", substr($header, 12, 1))[1]);
	$ret['readings'] = array();

	foreach($itemstrings as $chunk)
	{
		$item = array();
		$item['user_id'] = unpack("V", substr($chunk, 16, 4))[1];

		if(($item['user_id'] > 0) && ($ignore_registered_users)) { continue; }

		$item['weight_kg'] = ((unpack("V", substr($chunk, 8, 4))[1]) / 1000);
		$item['weight_st'] = ((unpack("V", substr($chunk, 8, 4))[1]) / 6350.293);
		$item['weight_lbs'] = ((unpack("V", substr($chunk, 8, 4))[1]) / 453.6);
		$item['date'] = unpack("V", substr($chunk, 12, 4))[1];
		$item['friendly_date'] = gmdate("l jS F Y, g:ia", $item['date']) . " UTC";
		$item['impedance'] = unpack("V", substr($chunk, 4, 4))[1];
		$item['body_fat_1'] = unpack("V", substr($chunk, 20, 4))[1];
		$item['body_fat_2'] = unpack("V", substr($chunk, 28, 4))[1];
		$item['covariance'] = unpack("V", substr($chunk, 24, 4))[1];

		$ret['readings'][] = $item;
	}

	return($ret);
}

function spoof_data($postdata)
{

	$ret = "";
	$ret .= substr($postdata, 0, 48);
	$postdata = substr($postdata, 48);
	while(strlen($postdata) >= 32)
	{
		$chunk = substr($postdata, 0, 32);
		$postdata = substr($postdata, 32);

		$random_weight = pack("V", rand(114000, 150000));
		$user_id = unpack("V", substr($chunk, 14, 4))[1];

		if($user_id == 0)
		{
			$chunk = substr($chunk, 0, 6) . $random_weight . substr($chunk, 10);
		}

		$ret .= $chunk;
	}
	$ret .= $postdata;

	$ret = substr($ret, 0, (strlen($ret) - 2));
	$crc = crc16($ret);
	$ret .= pack("v", $crc);

	return($ret);
}

function get_timestamp()
{
	$dt = time();
	$ret = pack("V", $dt);
	return($ret);
}

function crc16($buffer)
{
	$result = 0x0000;
	if (($length = strlen($buffer)) > 0)
	{
		for ($offset = 0; $offset < $length; $offset++)
		{
			$result ^= (ord($buffer[$offset]) << 8);
			for ($bitwise = 0; $bitwise < 8; $bitwise++)
			{
				if (($result <<= 1) & 0x10000) { $result ^= 0x1021; }
				$result &= 0xFFFF;
			}
		}
	}
	return $result;
}
