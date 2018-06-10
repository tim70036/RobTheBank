<?php
// Request: GET /symbols?symbol=<symbol>

// symbol: string. Symbol name or ticker.
// Example: GET /symbols?symbol=AAL, GET /symbols?symbol=NYSE:MSFT

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['symbol']))
{
	# Init Return Obj
	$responseObj = new stdClass();


	$responseObj->name = $_GET['symbol'];

	# Read JSON
	$jsData = '';
	$fileName = 'data/stockList.json';
	$jsData = file_get_contents($fileName);

	# Decode JSON
	$stockList = json_decode($jsData, true);

	# Find the target symbol
	foreach($stockList as $stock)
	{
		$symbol = strval($stock["symbol"]);
		if($symbol ===  $_GET['symbol'])
		{
			$responseObj->description = $symbol;
			$responseObj->exchange = $stock["name"] . "(" . $stock["industry"] .", " .  $stock["market"] . ")";

			#echo $description;
			break;
		}
		
	}

	
	$responseObj->type = 'stock'; // Possible Val : stock, index, forex, futures, bitcoin, expression, spread, cfd
	$responseObj->session = '0900-1330'; // Trading hours :  Mo-Fr 09:00-13:30
	$responseObj->timezone = 'Asia/Taipei';
	$responseObj->supported_resolutions = ["1D"];

	echo json_encode($responseObj);
}
	
?>