<?php
# Check login, if not, exit
require_once('authenticate.php');

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
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	
	$stock = filter_input (INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);

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

			# Validation, prevent shit time
			$dateObj = DateTime::createFromFormat('d.m.Y H:i', "10.10.2010 " . $timeStr);
		    if ( !($dateObj !== false && $dateObj && $dateObj->format('G') == intval($timeStr)) )
		    {
		    	# Set it to the start time of trading session, if it is invalid time
				$timeStr = "09:00";
		    }

			$hour = intval(substr($timeStr, 0 , 2));
			$min = intval(substr($timeStr, 3, 2));

			# Validation, trading session is from 9:00 ~ 13:30
			if($hour > 13)
				$timeStr = "13:30";
			else if($hour < 9)
				$timeStr = "09:00";
			else if($hour === 13 && $min > 30)
				$timeStr = "13:30";
			
			#var_dump($timeStr);

			# Conver time into Unix timestamp
			$data['timestamp'] = strtotime($dateStr . ' ' . $timeStr);

			$data['type'] = $_POST['buyOrSell' . $key];
			$data['amount'] = $_POST['amount' . $key];
			$data['price'] = $_POST['price' . $key];
			array_push($dataArray, $data);
		}
	}
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
        interval: 'D',
        container_id: "tv_chart_container",
        //  BEWARE: no trailing slash is expected in feed URL
        datafeed: new Datafeeds.UDFCompatibleDatafeed("/charting_server"),
        library_path: "charting_library/",
        //  Regression Trend-related functionality is not implemented yet, so it's hidden for a while
        drawings_access: { type: 'black', tools: [ { name: "Regression Trend" } ] },
        disabled_features: [ "header_symbol_search", "header_compare"],//"chart_scroll"
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
	$backgroundColor = ($data['type'] === 'sell') ? '#00e600' : '#e60000';

	echo "
	    	chart.createShape(
	    		{time: {$data["timestamp"]}, price: {$data["price"]} },
	    		{
	    			shape: 'price_label',
	                lock: true,
	                disableSelection: true,
					disableUndo: true,
					zOrder: 'top',
					overrides: 
					{
						backgroundColor: '{$backgroundColor}' ,
						color: '#ffffff',
						borderColor	:  '#8c8c8c',
						fontsize: 13
					}
	    		}
	    		);
    	";
}
?>

    });
</script>

<script type="text/javascript">

<?php 
# Set js var 
$user = $wrapper->getUser();
echo 'var userRecord = { "user" : "' . $user['Username'] . '" };' . "\n";
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
				['ins',['hr', 'picture', 'link', 'video']],
				['table',['table']],
				['Misc', ['fullscreen', 'undo', 'redo', 'help']]
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
			postData.push(userRecord);
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
               url: '/charting_server/recordCreate.php',
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
               		var message = "status : " + result["status"] + " " + result["statusText"] + "\\n";
               		message = message + "error : " + result["responseText"] + "\\n";
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