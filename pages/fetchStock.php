<?php

	require('config.php');
	
	$stockId = $_POST['search'];

						
	mysqli_select_db($connection,DB_DATABASE);
	mysqli_query($connection, "SET NAMES utf8");

	$output = '';
	$sql = "SELECT * FROM stockInfo WHERE stockId LIKE '%" .$stockId."%'";

	$result = mysqli_query($connection, $sql);
	if(mysqli_num_rows($result) > 0){

		while($row = mysqli_fetch_array($result)){
			$output .= '
				<tr>
					<td>' .$row["stockId"]. '</td>
					<td>' .$row["stockName"]. '</td>
					<td>' .$row["market"]. '</td>
					

				</tr>

			';
		}
		echo "<script> alert($stockId) </script>";
		echo $output;

	}else{
		echo 'Data Not Found';
	}


?>


