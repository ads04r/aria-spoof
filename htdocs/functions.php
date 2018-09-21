<?php

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

