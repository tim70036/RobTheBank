<?php
require_once('credentials.php');
require_once('../vendor/autoload.php');
require_once('AWSCognitoWrapper.php');
require_once('util.php');


use AWSCognitoApp\AWSCognitoWrapper;

$wrapper = new AWSCognitoWrapper();
$wrapper->initialize();

if(!$wrapper->isAuthenticated()) {
	http_response_code(500);
	echo "Authentication failed.";
	exit;
}
?>