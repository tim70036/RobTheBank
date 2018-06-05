<?php
# Receive form data and Process it
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])
{
	# Get id
	$id = filter_input (INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

	# Fetch entry database
	include_once("../dbinfo.inc");
	try
	{
		$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		if (mysqli_connect_errno())
		{
			throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());
		}

		# Prepare query
		$sql = "SELECT * FROM UserRecords WHERE id=$id";

		# Execute query
		$result = $connection->query($sql);

	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
}

?>

<?php
# Check login, if not, exit
require_once('authenticate.php');

# Print HTML content
require_once('html.php');
head(true);
?>