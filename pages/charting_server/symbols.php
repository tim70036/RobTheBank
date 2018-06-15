<?php
// Request: GET /symbols?symbol=<symbol>

// symbol: string. Symbol name or ticker.
// Example: GET /symbols?symbol=AAL, GET /symbols?symbol=NYSE:MSFT

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['symbol']))
{
	# Init Return Obj
	$responseObj = new stdClass();


	$responseObj->name = $_GET['symbol'];
	$responseObj->ticker =  $_GET['symbol'];

	# Fisrt search through SEM 
	# Read JSON data
	$fileName = 'data/stockListSEM.json';
	$stockList = json_decode(file_get_contents($fileName), true);

	# Find the target symbol
	$found = false;
	foreach($stockList as $stock)
	{
		$symbol = strval($stock["symbol"]);
		if($symbol ===  $_GET['symbol'])
		{
			$responseObj->description = $stock["name"] . "(" . $stock["industry"] . ")";
			$responseObj->exchange = $stock["market"];

			$found = true;
			break;
		}
		
	}

	# Not found in SEM , then search OTC
	if(!$found)
	{
		$fileName = 'data/stockListOTC.json';
		$stockList = json_decode(file_get_contents($fileName), true);
		foreach($stockList as $stock)
		{
			$symbol = strval($stock["symbol"]);
			if($symbol ===  $_GET['symbol'])
			{
				$responseObj->description = $stock["name"] . "(" . $stock["industry"] . ")";
				$responseObj->exchange = $stock["market"];
				break;
			}
			
		}
	}

	
	$responseObj->type = 'stock'; // Possible Val : stock, index, forex, futures, bitcoin, expression, spread, cfd
	$responseObj->session = '0900-1330'; // Trading hours :  Mo-Fr 09:00-13:30
	$responseObj->timezone = 'Asia/Taipei';
	$responseObj->supported_resolutions = ["1D"];
	//$responseObj->has_empty_bars = true;
	$responseObj->minmov = 1;
	$responseObj->pricescale = 100;

	echo json_encode($responseObj);
}
	
?>