<?php
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$responseObj = new stdClass();
	$responseObj->supported_resolutions = ['1', '5', '15', '30', '60', '1D', '1W', '1M'];
	#$responseObj->supported_resolutions = ['1'];
	$responseObj->supports_group_request = false;
	$responseObj->supports_marks = false;
	$responseObj->supports_search = true;
	$responseObj->supports_timescale_marks = false;

	echo json_encode($responseObj);
}
?>
