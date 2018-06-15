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
# Get username
$userName = ($wrapper->getUser())['Username'];

# Fetch from database
include_once("../dbinfo.inc");
try
{
	$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	if (mysqli_connect_errno())		throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());

	# Prepare query
	$sql = "SELECT id , stockId , createTime, updateTime FROM UserRecords WHERE userName='$userName'";

	# Execute query
	$result = $connection->query($sql);

	# Check if error occurred 
	if(!$result)	throw new Exception("Select query failed.");

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
?>


<!-- HTML Content -->
<!-- Datatable Library -->
<link rel="stylesheet" type="text/css" href="../dist/css/datatable/datatables.css">
<link rel="stylesheet" type="text/css" href="../dist/css/datatable/responsive.dataTables.css">
<script type="text/javascript" charset="utf8" src="../dist/js/datatable/datatables.js"></script>
<script type="text/javascript" charset="utf8" src="../dist/js/datatable/dataTables.responsive.js"></script>

<table id="record-table" class="display" style="width:100%">
    <thead>
        <tr>
        	<th></th>
            <th>日期</th>
            <th>股票</th>
            <th>最後修改時間</th>
            <th></th>
        </tr>
    </thead>
    <tbody>

<?php
# Print out Data
while($row = $result->fetch_assoc())
{
	$id = $row['id'];

	$dateObj = toTwTime($row['createTime']);
	$date = $dateObj->format('Y / n / j');
	
	$dateObj = toTwTime($row['updateTime']);
	$modifyTime = $dateObj->format('Y-m-d H:i:s');

	$stock = $row['stockId'];
	
	echo "
		<tr id=\"row$id\">
			<td>	<a href=\"recordHisEntry.php?id=$id\" class=\"btn btn-info\">查看</a>		</td>
            <td> $date </td>
            <td> $stock </td>
            <td> $modifyTime </td>
            <td>	<button id=\"btn$id\"onclick=\"DeleteRow($id, '$date', '$stock')\" class=\"btn btn-danger\">刪除</button>		</td>
        </tr>
		"; 
}

?>

    </tbody>
</table>

<!-- Datatable Init -->
<script type="text/javascript">

var table;
	$(document).ready( function () {

    	table  = $('#record-table').dataTable({

    		// Column definition
    		"columnDefs": [
				{
					"className": "dt-center", 
					"targets": [0]
				},
				{
					"className": "dt-right", 
					"targets": [4]
				},
				{ "width": "10%", "targets": 0 },
				{ "width": "30%", "targets": 1 },
				{ "width": "20%", "targets": 2 },
				{ "width": "30%", "targets": 3 },
				{ responsivePriority: 2, targets: 0 },
				{ responsivePriority: 1, targets: 1 },
				{ responsivePriority: 3, targets: 2 },
				{ responsivePriority: 4, targets: 3 }
        	]

		});
	});
</script>

<!-- Delete row -->
<script type="text/javascript">

	function DeleteRow(id, date, stock)
	{

		// Double check
		var result = confirm("確定刪除 " + date + " 股票代碼 " + stock + " 的日誌？");

		if(result == true)
		{
			$("#btn" + id).addClass("disabled");

			$.ajax({
	               type: "GET",
	               url: "charting_server/recordDelete.php?id=" + id,
	               // Delete that row if success
	               success: function(data)
	               {
	               		$("#row" + id).fadeOut(550, function(){$(this).remove()}); // Remove after fade out
	               },
	               // Alert if error
	               error: function(result) 
	               {
	                    var message = "status : " + result["status"] + " " + result["statusText"] + "\n";
	               		message = message + "error : " + result["responseText"] + "\n";
				    	alert(message + "Please try later...");
				    	$("#btn" + id).removeClass("disabled");
	               }
	        });
		}
		
	}
</script>

<?php
tail();
?>
