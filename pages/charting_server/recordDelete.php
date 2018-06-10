<?php

#Check if it is valid access
require_once('authenticate.php');

# Include some util functions
require_once('util.php');

# Save chart and record
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	try
	{
		# Get delete id
		$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
		if(!$id) throw new Exception("Invalid id");

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
		$sql = "DELETE FROM UserRecords WHERE id=$id AND userName='$userName'";

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