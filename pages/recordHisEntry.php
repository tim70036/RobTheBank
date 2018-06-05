<?php
$record = NULL;
# Receive form data and Process it
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']))
{
	# Get id and stock id from GET para
	$id = filter_input (INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

	# Fetch entry from database
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

		# Check if error occurred
		if(!$result)						throw new Exception("Select query failed.");
		if(mysqli_num_rows($result) < 1)	throw new Exception("No data returned.");

		# Get the record
		$record = $result->fetch_assoc();
		if(!$record)						throw new Exception("No data record.");

		# Close connection
		$connection->close();
	}
	catch(Exception $e)
	{
		$message = $e->getMessage();
		echo "
		<script>
			alert('$message, redirecting to home page...');
			window.location.href='index.php';
		</script>
		";
		exit;
	}
}
else
{
	# Redirect if it is illegal access

	echo "
		<script>
			alert('Illegal access, redirecting to home page...');
			window.location.href='index.php';
		</script>
		";
	exit;
}

?>

<?php
# Check login, if not, exit
require_once('authenticate.php');

# Include some util func for decoding data from DB
require_once('util.php');

# Print HTML content
require_once('html.php');
head(true);
?>

<!-- HTML Content -->
<!-- Charting Library -->
<script type="text/javascript" src="charting_library/charting_library.min.js"></script>
<script type="text/javascript" src="../datafeeds/udf/dist/polyfills.js"></script>
<script type="text/javascript" src="../datafeeds/udf/dist/bundle.js"></script>

<!-- Container -->
<div class="container">
	<div class="row">
		<div class="col-lg-7">
			<div id="tv_chart_container"></div>
		</div>
		<div class="col-lg-5">

<?php
	echo unicode_decode($record["userRecord"]);
?>
			<button id="save-btn" name="singlebutton" class="btn btn-primary">確認提交</button>
		</div>
	</div>
</div>

<!-- Widge setting -->
<script type="text/javascript">
	
    var widget = window.tvWidget = new TradingView.widget({
        // debug: true, // uncomment this line to see Library errors and warnings in the console
        fullscreen: true,
        symbol: 'BTC',
        debug: true,
        interval: 'D',
        container_id: "tv_chart_container",
        //  BEWARE: no trailing slash is expected in feed URL
        datafeed: new Datafeeds.UDFCompatibleDatafeed("/charting_server"),
        library_path: "charting_library/",
        //  Regression Trend-related functionality is not implemented yet, so it's hidden for a while
        drawings_access: { type: 'black', tools: [ { name: "Regression Trend" } ] },
        disabled_features: [ "header_symbol_search", "header_compare", ""],
        //enabled_features: ["study_templates"],
        charts_storage_url: 'http://saveload.tradingview.com',
        charts_storage_api_version: "1.1",
        client_id: 'tradingview.com',
        user_id: 'public_user_id',
        // Language
        locale: "zh_TW"
    });

    /* Call method after the chart is ready */
    widget.onChartReady(function(){

    	/* Set the chart record by server*/
<?php   echo "var chartState = JSON.parse('" . $record["chartRecord"] . "');" ?>
		
		/* Load chart record to the chart */
    	//console.log(chartState);
    	widget.load(chartState);
    });

</script>

<?php
tail();
?>