<?php 			
					require('config.php');

					$stockGroup = $_POST["stockGroup"];
					$userName = $_POST['userName'];


					mysqli_select_db($connection,DB_DATABASE);
					$sql="SELECT * FROM UserGroup WHERE userName = '$userName' AND groupId = '$stockGroup'";
					$result = mysqli_query($connection,$sql);
					echo "<script>";
					echo "stockList = [];";
					while($row = mysqli_fetch_array($result)) {
						if($row['stockId'] != "0") echo "stockList.push(\"" . $row['stockId'] . "\");";
					}
					echo "console.log(\"stockList:\" + stockList);";
					echo "</script>";

?>

<script>

	refreshStockData();

</script>

