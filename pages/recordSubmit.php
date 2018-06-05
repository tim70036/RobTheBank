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
	         	//return false;
				echo 'invalid';
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

<!-- Summernote Plugin -->
<script type="text/javascript" src="../dist/js/summernote.js"></script>
<script type="text/javascript" src="../dist/js/summernote-zh-TW.js"></script>
<link href="../dist/css/summernote/summernote.css" rel="stylesheet">

<div class="container">
	<div class="row">
		<div class="col-lg-7">
			<div id="tv_chart_container"></div>
		</div>
		<div class="col-lg-5">
			<form id="record-form" method="post">
				<textarea id="summernote" name="editordata"></textarea>
			</form>
			<button id="save-btn" name="singlebutton" class="btn btn-primary">確認提交</button>
		</div>
	</div>
</div>
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
				postData.push(state);
			});
			
			/* Turn array into JSON string */
			postData = JSON.stringify(postData);
   			//console.log(postData);

			$.ajax({
               type: "POST",
               contentType: "application/json; charset=utf-8",
               url: '/charting_server/record.php',
               data: postData, // The data
               success: function(data)
               {
                    // If save record successfully
                    if(data == "succeed")
                    {
                        window.location.replace('index.php');
                    }
                    // Else show alert
                    else
                    {
                    	console.log(data);
                    	$("#save-btn").removeClass("disabled");
                    }   
               }
             });

		});

		/* Init */
		$('#wrapper').removeClass("toggled");
		$('#summernote').summernote({
			lang: 'zh-TW',
			minHeight: 300
		});
	});
</script>

<?php
tail();
?>




