<?php
	require('config.php');

	try{

		$data = $_POST['data'];
		$userName = $_POST['userName'];
		mysqli_select_db($connection,DB_DATABASE);
		mysqli_query($connection, "SET NAMES utf8");


		for($col = 0; $col < count($data); $col++){
			$sql = "UPDATE UserStock SET sup1={$data[$col][1]}, sup2={$data[$col][2]}, sup3={$data[$col][3]},
									res1={$data[$col][4]} ,res2={$data[$col][5]} ,res3={$data[$col][6]} 
									WHERE userName='$userName' AND stockId={$data[$col][0]}";
			$result = mysqli_query($connection,$sql);

		}

	}catch(Exception $e){
		echo $e->getMessage();
	}
	


?>