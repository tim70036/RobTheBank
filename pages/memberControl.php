<?php
use AWSCognitoApp\AWSCognitoWrapper;
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ProcessType']))
{
    require_once('credentials.php');
    require_once('../vendor/autoload.php');
    require_once('AWSCognitoWrapper.php');
    
    # Include some util functions
    require_once('util.php');

    $wrapper = new AWSCognitoWrapper();
    $wrapper->initialize();

    $error = '';

    try
    {
        if($_POST['ProcessType'] === 'register')
        {
            $account = filter_input(INPUT_POST, 'account');
            $password = filter_input(INPUT_POST, 'password');
            $password2 = filter_input(INPUT_POST, 'password2');
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

            /* Checking */
            if(!$account) throw new Exception("Invalid Account!");
            if(!$password) throw new Exception("Invalid Password!");
            if(!$email) throw new Exception("Invalid Email!");
            if(strcmp($password, $password2) != 0) throw new Exception("Please Confirm Password!");

            /* Register through AWS */
            $error = $wrapper->signup($account, $email, $password);
        }
        else if($_POST['ProcessType'] === 'confirm')
        {
            $account = filter_input(INPUT_POST, 'account');
            $confirmation = filter_input(INPUT_POST, 'confirmation');
            
            /* Checking */
            if(!$account) throw new Exception("Invalid Account!");
            if(!$confirmation) throw new Exception("Invalid Confirmation Code!");

            /* Register through AWS */
            $error = $wrapper->confirmSignup($account, $confirmation);
        }
        else if($_POST['ProcessType'] === 'resendConfirm')
        {
            $account = filter_input(INPUT_POST, 'account');

            /* Checking */
            if(!$account) throw new Exception("Invalid Account!");

            $error = $wrapper->resendConfirmCode($account);
        }
        else if($_POST['ProcessType'] === 'login')
        {
            $account = filter_input(INPUT_POST, 'account');
            $password = filter_input(INPUT_POST, 'password');

            /* Checking */
            if(!$account) throw new Exception("Invalid Account!");
            if(!$password) throw new Exception("Invalid Password!");

            /* Login through AWS */
            $error = $wrapper->authenticate($account, $password);
        }
        else if($_POST['ProcessType'] === 'forget')
        {
            $account = filter_input(INPUT_POST, 'account');

            /* Checking */
            if(!$account) throw new Exception("Invalid Account!");

            $error = $wrapper->sendPasswordResetMail($account);
        }
        else if($_POST['ProcessType'] === 'reset')
        {
            $confirmation = filter_input(INPUT_POST, 'confirmation');
            $account = filter_input(INPUT_POST, 'account');
            $password = filter_input(INPUT_POST, 'password');
            $password2 = filter_input(INPUT_POST, 'password2');
            

            /* Checking */
            if(!$confirmation) throw new Exception("Invalid Confirmation Code!");
            if(!$account) throw new Exception("Invalid Account!");
            if(!$password) throw new Exception("Invalid Password!");
            if(strcmp($password, $password2) != 0) throw new Exception("Please Confirm Password!");

            /* Register through AWS */
            $error = $wrapper->resetPassword($confirmation, $password,$account);
        }
        else if($_POST['ProcessType'] === 'logout')
        {
            $wrapper->logout();
        }
        

        /* Check whether it is success*/
        if(empty($error)) 
        {
            /* Return successful code */
            http_response_code(200);
            exit;
        }
        else 
        {
            throw new Exception($error);
        }
    }
    catch(Exception $e)
    {
        /* Set error code */
        http_response_code(500);
        echo $e->getMessage();
        exit;
    }
}
?>
