<?php

require('config.php');

try{
	$groupId = $_POST['groupId'];
	$stockSymbol = $_POST['stockSymbol'];
	$userName = $_POST['userName'];


						
	mysqli_select_db($connection,DB_DATABASE);
	mysqli_query($connection, "SET NAMES utf8");
	$sql = "SELECT groupName FROM UserGroup WHERE userName='$userName' AND groupId='$groupId' ";
	$result = mysqli_query($connection,$sql);
	$row = mysqli_fetch_array($result);
	$groupName = $row['groupName'];

	$sql="SELECT * FROM UserGroup WHERE groupId = '$groupId' AND userName = '$userName' AND stockId = '$stockSymbol'";
	$result = mysqli_query($connection,$sql);
	$len = mysqli_num_rows($result);

	if( $len == 0){
		$sql="INSERT INTO UserGroup VALUES('','$userName','$groupId','$groupName','$stockSymbol')";
		$result = mysqli_query($connection,$sql);

		$sql="SELECT * FROM UserStock WHERE userName = '$userName' AND stockId = '$stockSymbol'";
		$result = mysqli_query($connection,$sql);
		$reminderLen = mysqli_num_rows($result);
		if($reminderLen == 0){
			$sql="INSERT INTO UserStock VALUES('','$userName','$stockSymbol','','','','','','')";
			$result = mysqli_query($connection,$sql);
		}

	}else{
		echo "<script>";
		echo "alert(\"此股票已存在\")";
		echo "</script>";
	}				
}catch(Exception $e){
		echo $e->getMessage();
}

						

?>