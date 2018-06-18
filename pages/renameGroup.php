<?php

	require('config.php');

	try{
		$newGroupName = $_POST['newGroupName'];
		$groupId = $_POST["groupId"];
		$userName = $_POST['userName'];
		
						
		mysqli_select_db($connection,DB_DATABASE);
		mysqli_query($connection, "SET NAMES utf8");



		$sql="SELECT DISTINCT groupName FROM UserGroup WHERE userName = '$userName' AND groupName = '$newGroupName'";

		$result = mysqli_query($connection,$sql);
		$len = mysqli_num_rows($result);

		if ($len == 0 AND $groupId != ""){
			$sql="UPDATE UserGroup SET groupName='$newGroupName' WHERE userName = '$userName' AND groupId = '$groupId' ";
			$result = mysqli_query($connection,$sql);
		}else{
			echo "<script>";
			echo "alert(\"此群組已存在\");";
			echo "</script>";
		}
	}catch(Exception $e){
		echo $e->getMessage();
	}


?>