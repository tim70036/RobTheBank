<?php
	require('config.php');
	try{

		$userName = $_POST['userName'];

		mysqli_select_db($connection,DB_DATABASE);
		mysqli_query($connection, "SET NAMES utf8");


		$sql = "SELECT stockId, sup1, sup2, sup3, res1, res2, res3, comment FROM UserStock WHERE userName = '$userName'";
		$result = mysqli_query($connection,$sql);

		$data = array();

		while($row = mysqli_fetch_array($result)){

				$data[] = array('stockId'=>$row['stockId'],
								'sup1'=>$row['sup1'],
								'sup2'=>$row['sup2'],
								'sup3'=>$row['sup3'],
								'res1'=>$row['res1'],
								'res2'=>$row['res2'],
								'res3'=>$row['res3'],
								'comment'=>$row['comment']
						);
		}


		echo json_encode($data);

	}catch(Exception $e){
			echo $e->getMessage();   
	}
?>