<?php
// Request: GET /symbols?symbol=<symbol>

# Include some util functions
require_once('util.php');

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['symbol']))
{

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
				$found = true;
				break;
			}
			
		}
	}

	
	if($found)
	{
		http_response_code(200);
	}
	else
	{
		http_response_code(500);
	}
}
	
?>