<?php
// Request: GET /symbols?symbol=<symbol>

// symbol: string. Symbol name or ticker.
// Example: GET /symbols?symbol=AAL, GET /symbols?symbol=NYSE:MSFT

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['symbol']))
{


	$responseObj = new stdClass();
	$responseObj->name = $_GET['symbol'];
	$responseObj->description = 'The testing description of symbol';
	$responseObj->type = 'stock'; // Possible Val : stock, index, forex, futures, bitcoin, expression, spread, cfd
	$responseObj->session = '0900-1330'; // Trading hours :  Mo-Fr 09:00-13:30
	$responseObj->exchange = 'TWSE';
	$responseObj->timezone = 'Asia/Taipei';
	$responseObj->supported_resolutions = ["1D"];

	echo json_encode($responseObj);
}

	
?>