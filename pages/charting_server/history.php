<?php
// Request: GET /history?symbol=<ticker_name>&from=<unix_timestamp>&to=<unix_timestamp>&resolution=<resolution>

// symbol: symbol name or ticker.
// from: unix timestamp (UTC) of leftmost required bar
// to: unix timestamp (UTC) of rightmost required bar
// resolution: string
// Example: GET /history?symbol=BEAM~0&resolution=D&from=1386493512&to=1395133512


#Check if it is valid access
require_once('authenticate.php');

# Include some util functions
require_once('util.php');

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	try
	{
		# Get parameters
		$symbol = filter_input(INPUT_GET, "symbol");
		$from = filter_input(INPUT_GET, "from", FILTER_SANITIZE_NUMBER_INT);
		$to = filter_input(INPUT_GET, "to", FILTER_SANITIZE_NUMBER_INT);

		# Check
		if(!($symbol && $from && $to) ) throw new Exception("Invalid parameter!");

		# Connect to database
		include_once("../../dbinfo.inc");
		$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		mysqli_query($connection, "SET NAMES utf8");

		# Check if error occurred
		if (mysqli_connect_errno())
		{
			throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());
		}
		
		# Prepare query
		$tableName = $symbol . "_Min";
		$sql = "SELECT * FROM `$tableName` WHERE timestamp >= $from AND timestamp <= $to ORDER BY timestamp ASC ;";

		# Execute query
		$result = $connection->query($sql);

		# Check if error occurred 
		if(!$result)	throw new Exception("Select query failed.");

		# Init response
		$responseObj = new stdClass();

		# If no data in this interval
		if(mysqli_num_rows($result) <= 0)
		{
			$responseObj->s = "no_data";

			$sql = "SELECT MAX(timestamp) AS maxtime, MIN(timestamp) AS mintime FROM `$tableName`";
			$result = $connection->query($sql);
			if(!$result)	throw new Exception("MIN MAX query failed.");

			$row = $result->fetch_assoc();
			if($from > $row['maxtime'])
				$responseObj->nextTime = (int)$row['maxtime'];
			else // $to < min time
				$responseObj->nextTime = (int)$row['mintime'];

		}
		# If there is data
		else
		{	
			# Gather Data
			$responseObj->s = "ok";
			$responseObj->t = array();
			$responseObj->c = array();
			$responseObj->o = array();
			$responseObj->h = array();
			$responseObj->l = array();
			$responseObj->v = array();
			while($row = $result->fetch_assoc())
			{
				$responseObj->t[] = (int)$row['timestamp'];
				$responseObj->c[] = (float)$row['close'];
				$responseObj->o[] = (float)$row['open'];
				$responseObj->h[] = (float)$row['high'];
				$responseObj->l[] = (float)$row['low'];
				$responseObj->v[] = (int)$row['volume'];
			}
		}

		# Response
		http_response_code(200);
		echo json_encode($responseObj);

		# Close connection
		$connection->close();

	}
	catch(Exception $e)
	{
		http_response_code(500);
		echo $e->getMessage();
		exit;
	}
}
?>