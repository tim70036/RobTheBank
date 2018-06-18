<?php

						require('config.php');

						$groupName = $_POST['groupName'];
						$userName = $_POST['userName'];

						
						mysqli_select_db($connection,DB_DATABASE);
						mysqli_query($connection, "SET NAMES utf8");

						//$sql="SELECT DISTINCT groupId FROM UserGroup WHERE userId = 1 and stockId = 0";
						$sql="SELECT MAX(groupId) FROM UserGroup WHERE userName = '$userName'";
						$result = mysqli_query($connection,$sql);
						$row = mysqli_fetch_array($result);
						$len = (int)$row[0] + 1;

						$sql="SELECT DISTINCT groupName FROM UserGroup WHERE userName = '$userName' AND groupName = '$groupName'";

						$result = mysqli_query($connection,$sql);
						$row1 = mysqli_fetch_array($result);
						
						if ($row1['groupName'] == "" AND $groupName != ""){
							echo "<script>";
							echo "console.log(\"len = \" + '$len');";
							echo "</script>";
							$sql="INSERT INTO UserGroup VALUES('','$userName','$len','$groupName','')";
							$result = mysqli_query($connection,$sql);
						}else{
							echo "<script>";
							echo "alert(\"此群組已存在\");";
							echo "</script>";
						}


?>