<?php
// Request: GET /history?symbol=<ticker_name>&from=<unix_timestamp>&to=<unix_timestamp>&resolution=<resolution>

// symbol: symbol name or ticker.
// from: unix timestamp (UTC) of leftmost required bar
// to: unix timestamp (UTC) of rightmost required bar
// resolution: string
// Example: GET /history?symbol=BEAM~0&resolution=D&from=1386493512&to=1395133512


//echo($responseStr);



$responseStr = file_get_contents("./bar.JSON");

$noMoreDataStr = <<<JSON
{
   "s": "no_data"
}
JSON;


if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['symbol']))
{

	
	if($_GET['from'] < 1463875200)
		echo $noMoreDataStr;
	else
		echo $responseStr;

	exit;
}
//echo $responseStr;
?>