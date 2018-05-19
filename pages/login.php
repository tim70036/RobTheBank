<?php
use AWSCognitoApp\AWSCognitoWrapper;
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    require_once('credentials.php');
    require_once('../vendor/autoload.php');
    require_once('AWSCognitoWrapper.php');
    
    $wrapper = new AWSCognitoWrapper();
    $wrapper->initialize();

    try
    {
        $account = filter_input(INPUT_POST, 'account');
        $password = filter_input(INPUT_POST, 'password');

        /* Checking */
        if(!$account) throw new Exception("Invalid Account!");
        if(!$password) throw new Exception("Invalid Password!");

        /* Login through AWS */
        $error = $wrapper->authenticate($account, $password);
        if(empty($error)) 
        {
            header('Location: secure.php');
            exit;
        }
        else 
        {
            throw new Exception($error);
        }

    }
    catch(Exception $e)
    {
        header('HTTP/1.1 400 Bad request'); 
        echo $e->getMessage();
    }
}

?>

<!-- Login content for popup.html -->
<div class="card-header" style="text-align: center; font-size: 1.4em;">登入</div>
<div class="card-body">
    <form method="post" action="login.php">
        <div class="form-group">
            <label for="InputAccount1">帳號</label>
            <input class="form-control" name='account' id="InputAccount1" type="text" aria-describedby="accountHelp" placeholder="請輸入帳號">
        </div>
        <div class="form-group">
            <label for="InputPassword1">密碼</label>
            <input class="form-control" name='password' id="InputPassword1" type="password" placeholder="請輸入密碼">
        </div>
        <div class="form-group">
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox"> 記住密碼 </label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">登入</button>
    </form>

    <!-- Change to another content -->
    <div class="text-center" style="margin-top: 10px;">
        <a class="d-block small mt-3" onclick="LoadRegister()" style="margin: 0px 10px;">註冊帳號</a>
        <a class="d-block small" href="forgot-password.html" style="margin: 0px 10px;">忘記密碼?</a>
    </div>
</div>
<!-- /.card-body -->