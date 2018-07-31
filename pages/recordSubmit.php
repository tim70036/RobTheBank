<?php
# Check login, if not, exit
require_once('authenticate.php');

# Include some util func for chking file
require_once('util.php');

# Print HTML content
require_once('html.php');
head(true);
?>

<?php

# Var init
$dataArray = array();
$maxTrans = 1000; # default at most 1000 transaction
$stock = 8787;


# Receive form data and Process it
try
{
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$stock = filter_input (INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);

		# Process data, 2 types of input
		# File input
		if($_POST["action"] === "file")
		{

			# Read each transaction's data out into $dataArray
			for($i=1 ; $i<$maxTrans ; $i++)
			{
				
				$key = strval($i);

				# Check whether exists data by checking hidden input value
				if(isset($_POST[$key]))
				{
					$data = array();

					$fileName = "recordFile" . $key;
					$dateStr = $_POST['date' . $key];

					# Validation , avoid dangerous file
					$errorMsg = "";
					if (checkFileUpload($fileName, $errorMsg) == false)
					{
						throw new Exception($errorMsg);
					}
					# Validation Succeed 
					else
					{
						// echo "日期: " . $dateStr  ."<br/>";
						// echo "編號: " . $fileName ."<br/>";
						// echo "檔案名稱: " . $_FILES[$fileName]["name"]."<br/>";
						// echo "檔案類型: " . $_FILES[$fileName]["type"]."<br/>";
						// echo "檔案大小: " . ($_FILES[$fileName]["size"] / 1024)." Kb<br />";
						// echo "暫存名稱: " . $_FILES[$fileName]["tmp_name"];

						$csv = array_map('str_getcsv', file($_FILES[$fileName]['tmp_name']));

						# Convert big5 -> utf8, since excel use big5
						arrayBig5ToUtf8($csv);

						# Iter each row data
						foreach($csv as $key=>$row)
						{
							# Skip the first row, since it is column header
							if($key === 0) continue;

							# Skip the undesired stocks
							$stockNum = substr($row[3], 0, 4);
							if(!is_numeric($stockNum)) 	throw new Exception("Wrong data foramt in csv.");
							if($stockNum !== $stock)	continue;

							# Extract time, we need only hour : minute
							$timeStr = $row[0];
							$timeStr = substr($timeStr, 0, 5);
							
							# Validate  time
							$timeStr = checkRecordTime($timeStr);

							# Validate
							if($row[2][0] !== '+' && ($row[2][0] !== '-'))		throw new Exception("Wrong data foramt in csv.");
							if(!is_numeric($row[6]) || !is_numeric($row[7]))	throw new Exception("Wrong data foramt in csv.");
							
							# Gather data into dataArray
							$timezone = 'Asia/Taipei';
							$data['timestamp'] = strtotime($dateStr . ' ' . $timeStr . $timezone); # Conver time into Unix timestamp
							$data['type'] = ($row[2][0] === '+') ? 'buy' : 'sell';
							$data['amount'] = $row[6];
							$data['price'] = $row[7];
							array_push($dataArray, $data);
						}
						
					}
				}
			}
			
		}
		# Manual input
		else
		{
			# Read each transaction's data out into $dataArray
			for($i=1 ; $i<$maxTrans ; $i++)
			{
				
				$key = strval($i);

				# Check whether exists data by checking hidden input value
				if(isset($_POST[$key]))
				{
					$data = array();

					$dateStr = $_POST['date' . $key];
					$timeStr = $_POST['time' . $key];

					# Validate the time
					$timeStr = checkRecordTime($timeStr);

					# Gather data into dataArray
					$timezone = 'Asia/Taipei';
					$data['timestamp'] = strtotime($dateStr . ' ' . $timeStr . $timezone); # Conver time into Unix timestamp
					$data['type'] = $_POST['buyOrSell' . $key];
					$data['amount'] = $_POST['amount' . $key];
					$data['price'] = $_POST['price' . $key];
					array_push($dataArray, $data);
				}
			}
		}
		
		# Testing
		// var_dump($dataArray);
		// exit;
	}
}
catch(Exception  $e)
{
	echo "Error: " . $e->getMessage();
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
		# Print out Data
		foreach($dataArray as $data)
		{
			
			$type = $data['type'];
			$amount = $data['amount'];
			$price = $data['price'];

			$timezone = 'Asia/Taipei';
			$time = new DateTime();
			$time->setTimestamp($data['timestamp']);
			$time->setTimezone(new DateTimeZone($timezone));
			$time = $time->format('Y-m-d H:i');
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
	<div class="col-lg-8" >
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
				<button id="save-btn" name="singlebutton" class="btn btn-primary">確認提交</button>
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
        //fullscreen: true,
        autosize: true,
        symbol: '<?php echo $stock; ?>', // echo stock from server
        debug: true,
        interval: '5',
        timeframe: '5D',
        container_id: "tv_chart_container",
        //  BEWARE: no trailing slash is expected in feed URL
        datafeed: new Datafeeds.UDFCompatibleDatafeed("charting_server", 100*1000),
        library_path: "charting_library/",
        //  Regression Trend-related functionality is not implemented yet, so it's hidden for a while
        drawings_access: { type: 'black', tools: [ { name: "Regression Trend" } ] },
        disabled_features: [ "header_symbol_search", "header_compare", "use_localstorage_for_settings"],//"chart_scroll"
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

    	var chart = widget.chart();


<?php
# Create price label 
foreach($dataArray as $data)
{
	$backgroundColor = ($data['type'] === 'sell') ? '#00cc00' : '#e60000';
	echo "chart.createShape(
	    		{time : {$data["timestamp"]} , price : {$data["price"]} } ,
	    		{
	    			shape: 'price_label',
	                lock: true,
	                disableSelection: true,
					disableUndo: true,
					zOrder: 'top',
					overrides: 
					{
						backgroundColor: '{$backgroundColor}' ,
						color: '#0073e6',
						borderColor	:  '#8c8c8c',
						fontsize: 10,
						transparency: 80
					}
	    		});
    	";
}

?>

    });
</script>

<script type="text/javascript">

<?php 
# Set js var 
echo 'var stockRecord = { "stockId" : ' . $stock . " }; \n";
echo 'var transRecord = '. json_encode($dataArray) . " ; \n"; 
?>

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
				['ins',['hr', 'link']],
				['table',['table']],
				['Misc', ['undo', 'redo', 'help']]
			]
		});

		/* Datatable */
		$('#record-table').dataTable({

			// No search bar
			searching: false,

			// No page
			paging: false,

			// No info
			info: true
		});

		/* Submit the form and save the chart to server*/
		$('#save-btn').click(function(e){

			$("#save-btn").addClass("disabled");

			/* Gather Data */
			var postData = new Array();
			postData.push(stockRecord);
			postData.push(transRecord);
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
               url: 'charting_server/recordCreate.php',
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