<?php 			
	require('config.php');
			try{
				$stockSymbol = $_POST["stockSymbol"];
				$userName = $_POST['userName'];
				$groupId = $_POST["groupId"];

				mysqli_select_db($connection,DB_DATABASE);

				$symbols = implode("','", $stockSymbol);;
				$sql="DELETE FROM UserGroup WHERE userName = '$userName' AND groupId = '$groupId' AND stockId IN ('".$symbols."')";
				$result = mysqli_query($connection,$sql);

				$sql="SELECT DISTINCT stockId FROM UserGroup WHERE userName = '$userName' AND stockId NOT IN ('".$symbols."')";
				$result = mysqli_query($connection,$sql);

				$arr = array();
				while($row = mysqli_fetch_array($result)){
					array_push($arr, $row['stockId']);
				}

				$symbols = implode("','", $arr);
				$sql="DELETE FROM UserStock WHERE userName = '$userName' AND stockId IN ('".$arr."')";
				$result = mysqli_query($connection,$sql);
			}catch(Exception $e){
				echo $e->getMessage();
			}
					
					
					
?>