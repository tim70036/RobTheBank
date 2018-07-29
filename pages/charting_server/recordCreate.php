<?php

#Check if it is valid access
require_once('authenticate.php');

# Include some util functions
require_once('util.php');

# Save chart and record
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	
	try
	{
		# Get JSON from this special place
		$json = file_get_contents('php://input');
		
		# Decode JSON string to array
		$data = json_decode($json, true);
		// var_dump($data);

		# Take out each field in data array
		$stockId = $data[0]['stockId'];
		$transRecord = $data[1];
		$userRecord = $data[2][0]['value'];
		$chartRecord = $data[3];

		# Encode array into JSON string
		$stockId 		=  json_encode($stockId);
		$transRecord	=  json_encode($transRecord);
		$userRecord 	=  json_encode($userRecord);
		$chartRecord 	=  json_encode($chartRecord);

		# Trim double quote
		$userRecord = trim($userRecord, '"');

		# Get user name
		$userName = ($wrapper->getUser())['Username'];

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
		$sql = "INSERT INTO UserRecords(userName, stockId, transRecord, userRecord, chartRecord) VALUES ('$userName', $stockId, '$transRecord', '$userRecord', '$chartRecord')";

		# Execute query
		if($connection->query($sql) === TRUE)
		{
			# Debug usage
			print $userName;
			print $stockId;
			print $transRecord;
			print $userRecord;
			print $chartRecord;

			# Return successful response
			http_response_code(200);
		}
		else
		{
			throw new Exception("Error: " . $sql . "<br>" . $connection->error);
		}

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