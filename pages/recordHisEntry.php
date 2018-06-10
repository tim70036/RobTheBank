<?php
# Check login, if not, exit
require_once('authenticate.php');

# Include some util func for decoding data from DB
require_once('util.php');

# Print HTML content
require_once('html.php');
head(true);
?>

<?php
$record = NULL;

# Get username
$userName = ($wrapper->getUser())['Username'];

# Process url to get data from database
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']))
{
	# Get id from GET para
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
		$sql = "SELECT * FROM UserRecords WHERE id=$id AND userName='$userName'";

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



<!-- HTML Content -->
<!-- Charting Library -->
<script type="text/javascript" src="charting_library/charting_library.min.js"></script>
<script type="text/javascript" src="../datafeeds/udf/dist/polyfills.js"></script>
<script type="text/javascript" src="../datafeeds/udf/dist/bundle.js"></script>
<link href="../dist/css/chart.css" rel="stylesheet">

<!-- Summernote Plugin -->
<script type="text/javascript" src="../dist/js/summernote.js"></script>
<script type="text/javascript" src="../dist/js/summernote-zh-TW.js"></script>
<link href="../dist/css/summernote/summernote.css" rel="stylesheet">

<!-- Datatable Library -->
<link rel="stylesheet" type="text/css" href="../dist/css/datatable/datatables.css">
<link rel="stylesheet" type="text/css" href="../dist/css/datatable/responsive.dataTables.css">
<script type="text/javascript" charset="utf8" src="../dist/js/datatable/datatables.js"></script>
<script type="text/javascript" charset="utf8" src="../dist/js/datatable/dataTables.responsive.js"></script>

<!-- Trans Record Table -->
<div class="row">
	<div class="col-lg-12" >
		<caption> <h3> 交易明細 </h3> </caption>
		<table id="record-table" class="display" style="width:100%">
		    <thead>
		        <tr>
		        	<th>時間</th>
		            <th>單類</th>
		            <th>張數</th>
		            <th>價格</th>
		        </tr>
		    </thead>
		    <tbody>

		<?php
		# Process the data from server into array
		$dataArray = json_decode($record['transRecord'], true);

		# Print out Data
		foreach($dataArray as $data)
		{
			$time = $data['timestamp'];
			$type = $data['type'];
			$amount = $data['amount'];
			$price = $data['price'];

			$time = date('Y-m-d H:i',$time);
			$type = ($type === "buy") ? "買進" : "賣出";

			echo "
				<tr>
					<td> $time </td>
		            <td> $type </td>
		            <td> $amount </td>
		            <td> $price </td>
		        </tr>
				"; 
		}

		?>

		    </tbody>
		</table>
	</div>
</div>

<!-- Chart and UserRecord -->
<div class="row" style="margin-top: 50px;">
	<div class="col-lg-8">
		<div id="tv_chart_container"></div>
	</div>
	<!-- /. col 8 -->
	<div class="col-lg-4">
		<div class="row">
			<div class="col-lg-12">
				<form id="record-form" method="post">
					<textarea id="summernote" name="editordata"></textarea>
				</form>
			</div>
		</div>
		<!-- /. row -->
		<div class="row">
			<div class="col-lg-12 center">
				<button id="save-btn" name="singlebutton" class="btn btn-primary">提交修改</button>
			</div>
		</div>
		<!-- /. row -->
	</div>
	<!-- /. col 4 -->
</div>
<!-- /. row -->

<!-- Widge setting -->
<script type="text/javascript">
	
    var widget = window.tvWidget = new TradingView.widget({
        // debug: true, // uncomment this line to see Library errors and warnings in the console
        autosize: true,
        symbol: '<?php echo $record["stockId"]; ?>',
        debug: true,
        interval: 'D',
        container_id: "tv_chart_container",
        //  BEWARE: no trailing slash is expected in feed URL
        datafeed: new Datafeeds.UDFCompatibleDatafeed("charting_server"),
        library_path: "charting_library/",
        //  Regression Trend-related functionality is not implemented yet, so it's hidden for a while
        drawings_access: { type: 'black', tools: [ { name: "Regression Trend" } ] },
        disabled_features: [ "header_symbol_search", "header_compare", ""],
        enabled_features: ["side_toolbar_in_fullscreen_mode"],
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
		var chartState = JSON.parse('<?php echo unicode_decode($record["chartRecord"]); ?>');
		
		/* Load chart record to the chart */
    	widget.load(chartState);

    	/* Get the correct title, otherwise the title will be exchange:symbol */
    	// var chart = widget.chart();
    	// chart.setSymbol('<?php echo $record["stockId"]; ?>', '1D');
    });

</script>

<script type="text/javascript">
	$(document).ready(function() {

		/* Init */
		$('#wrapper').removeClass("toggled");

		/* Summer Note */
		$('#summernote').summernote({
			lang: 'zh-TW',
			height: 450,
			maxHeight: 450,
			placeholder: '請輸入日誌內容...',

			toolbar: [
				['first', ['style']],
				['style', ['bold', 'italic', 'underline', 'clear']],
				['color',['color']],
				['fontsize', ['fontsize']],
				['para', ['paragraph',  'ul', 'ol', 'height']],
				['ins',['hr', 'picture', 'link', 'video']],
				['table',['table']],
				['Misc', ['fullscreen', 'undo', 'redo', 'help']]
			]
		});

		/* Load Content to Editor */
		var contents = '<?php echo unicode_decode($record["userRecord"]); ?>';
		$('#summernote').summernote('code', contents);


		/* Datatable */
		$('#record-table').dataTable({

			// No search bar
			searching: false,

			// No page
			paging: false,

			// No info
			info: true
		});


<?php 
# Set js var 
$user = $wrapper->getUser();
echo 	'var userRecord = { "user" : "' . $user['Username'] . '" };' . "\n";
?>


		/* Submit the form and save the chart to server*/
		$('#save-btn').click(function(e){

			$("#save-btn").addClass("disabled");

			/* Gather Data */
			var postData = new Array();
			postData.push(userRecord);
			postData.push(<?php echo $id; ?>);
			postData.push($("#record-form").serializeArray());
			widget.save(function(state){
				//console.log(state);
				postData.push(state);
			});
			
			/* Turn array into JSON string */
			postData = JSON.stringify(postData);
   			//console.log(postData);

			$.ajax({
               type: "POST",
               contentType: "application/json; charset=utf-8",
               url: 'charting_server/recordUpdate.php',
               data: postData, // The data
               // Redirect if success
               success: function(data)
               {
                    window.location.replace('recordHisTable.php');
               },
               // Alert if error
               error: function(result) 
               {
               		//console.log(result);
               		var message = "status : " + result["status"] + " " + result["statusText"] + "\n";
               		message = message + "error : " + result["responseText"] + "\n";
			    	alert(message + "Please try later...");
			    	$("#save-btn").removeClass("disabled");
			  	}
             });

		});
	});
</script>

<?php
tail();
?>