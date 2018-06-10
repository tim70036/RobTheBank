<?php
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$responseObj = new stdClass();
	$responseObj->supported_resolutions = ['1D'];
	$responseObj->supports_group_request = false;
	$responseObj->supports_marks = false;
	$responseObj->supports_search = true;
	$responseObj->supports_timescale_marks = false;

	echo json_encode($responseObj);
}

	// $responseObj = new stdClass();
	// $responseObj->supported_resolutions = ['1D'];
	// $responseObj->supports_group_request = false;
	// $responseObj->supports_marks = false;
	// $responseObj->supports_search = true;
	// $responseObj->supports_timescale_marks = false;

	// echo json_encode($responseObj);
?>
