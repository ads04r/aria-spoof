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

		$random_weight = pack("V", rand(50000, 150000));
		$user_id = unpack("V", substr($chunk, 14, 4))[1];

		if($user_id == 0)
		{
			$chunk = substr($chunk, 0, 6) . $random_weight . substr($chunk, 10);
		}

		$ret .= $chunk;
	}
	$ret .= $postdata;

	return($ret);
}

