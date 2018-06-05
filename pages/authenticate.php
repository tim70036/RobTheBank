<?php
require_once('credentials.php');
require_once('../vendor/autoload.php');
require_once('AWSCognitoWrapper.php');

use AWSCognitoApp\AWSCognitoWrapper;

$wrapper = new AWSCognitoWrapper();
$wrapper->initialize();

if(!$wrapper->isAuthenticated()) {
	include_once('html.php');
	head(false);
    readfile("./notlogin.html"); 
    tail();  
    exit;
}
?>