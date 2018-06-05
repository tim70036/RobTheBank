<?php

# Save chart
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$json = file_get_contents('php://input');
	var_dump($json);
	//var_dump($_POST);
}

?>