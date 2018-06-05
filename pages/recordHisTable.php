<?php
# Check login, if not, exit
require_once('authenticate.php');

# Print HTML content
require_once('html.php');
head(true);
?>

<?php
# Some util functions
function replace_unicode_escape_sequence($match) 
{
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}
function unicode_decode($str) 
{
    return preg_replace_callback('/u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
}
function toTwTime($dateStr)
{
	$date = new DateTime($dateStr, new DateTimeZone('UTC'));
	$date->setTimezone(new DateTimeZone("Asia/Taipei"));
	return $date;
}
?>


<?php
# Get username
$userName = ($wrapper->getUser())['Username'];

# Fetch from database
include_once("../dbinfo.inc");
try
{
	$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	if (mysqli_connect_errno())
	{
		throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());
	}

	# Prepare query
	$sql = "SELECT id , stockId , time FROM UserRecords WHERE userName='$userName'";

	# Execute query
	$result = $connection->query($sql);

}
catch(Exception $e)
{
	echo $e->getMessage();
}
?>




<!-- HTML Content -->
<!-- Datatable Library -->
<link rel="stylesheet" type="text/css" href="../dist/css/datatables.css">
<script type="text/javascript" charset="utf8" src="../dist/js/datatables.js"></script>

<table id="record-table" class="display">
    <thead>
        <tr>
        	<th>ID</th>
            <th>日期</th>
            <th>股票</th>
            <th>建立時間</th>
        </tr>
    </thead>
    <tbody>

<?php
# Print out Data
while($row = $result->fetch_assoc())
{
	$id = $row['id'];
	$dateObj = toTwTime($row['time']);
	$date = $dateObj->format('Y / n / j');
	$createTime = $dateObj->format('Y-m-d H:i:s');
	$stock = $row['stockId'];
	
	echo "
		<tr>
			<td> $id </td>
            <td> $date </td>
            <td> $stock </td>
            <td> $createTime </td>
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

    		// Hide ID column
    		"columnDefs": [
				{
					"targets": [ 0 ],
					"visible": false,
					"searchable": false
				}
        	],

        	// Row link
        	"fnDrawCallback": function () {

				$('#record-table tbody tr').click(function () {

					// get position of the selected row
					var position = table.fnGetPosition(this);

					// value of the first column (can be hidden)
					var id = table.fnGetData(position)[0];

					// Open new tab
					//document.location.href = ;
					var url = '?id=' + id;
					window.open(url, '_blank');
				})

			}

		});
	});
</script>

<?php
tail();
?>
