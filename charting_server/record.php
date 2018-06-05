<?php

# Save chart
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	
	try
	{
		$json = file_get_contents('php://input');
		
		# Decode JSON string to array
		$data = json_decode($json, true);
		// var_dump($data);

		# Take out each field in data array
		$userName = $data[0]['user'];
		$stockId = $data[1]['stockId'];
		$transRecord = $data[2];
		$userRecord = $data[3][0]['value'];
		$chartRecord = $data[4];

		# Encode array into JSON string
		$userName 		=  json_encode($userName);
		$stockId 		=  json_encode($stockId);
		$transRecord	=  json_encode($transRecord);
		$userRecord 	=  json_encode($userRecord);
		$chartRecord 	=  json_encode($chartRecord);

		# Trim double quote
		$userName = trim($userName, '"');
		$userRecord = trim($userRecord, '"');

		# Connect to database
		include_once("../dbinfo.inc");
		$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		mysqli_query($connection, "SET NAMES utf8");

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
			// print $userName;
			// print $stockId;
			// print $transRecord;
			// print $userRecord;
			// print $chartRecord;

			# Return successful response
			print "succeed";
		}
		else
		{
			throw new Exception("Error: " . $sql . "<br>" . $connection->error);
			
		}
		$connection->close();

	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
}

?>