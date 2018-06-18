<?php 			
		require('config.php');

		try{
			$stockGroup = $_POST["stockGroup"];
			$userName = $_POST['userName'];
			$stockSymbol = $_POST['stockSymbol'];
				
			mysqli_select_db($connection,DB_DATABASE);

			

			$sql="DELETE FROM UserGroup WHERE userName = '$userName' AND groupId = '$stockGroup'";
			$result = mysqli_query($connection,$sql);

			$symbols = implode("','", $stockSymbol);
			$sql="SELECT DISTINCT stockId FROM UserGroup WHERE userName = '$userName' AND groupId = '$stockGroup' AND stockId NOT IN ('".$symbols."')";
			$result = mysqli_query($connection,$sql);

			$arr = array();
			while($row = mysqli_fetch_array($result)){
				array_push($arr, $row['stockId']);
			}

			$deleteSymbols = implode("','", $arr);
			$sql="DELETE FROM UserStock WHERE userName = '$userName' AND stockId IN ('".$deleteSymbols."')";
			$result = mysqli_query($connection,$sql);

		}catch(Exception $e){
			echo $e->getMessage();
		}
		
					
?>