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
        $confirmation = filter_input(INPUT_POST, 'confirmation');
        
        /* Checking */
        if(!$account) throw new Exception("Invalid Account!");
        if(!$confirmation) throw new Exception("Invalid Confirmation Code!");

        /* Register through AWS */
        $error = $wrapper->confirmSignup($account, $confirmation);
        if(empty($error)) 
        {
            echo 'succeed'; // Let front end check whether it is success
            exit;
        }
        else 
        {
            throw new Exception($error);
        }

    }
    catch(Exception $e)
    {
        echo $e->getMessage();
        exit;
    }
}

?>

<!-- Confirm content for popup.html -->
<div class="card-header" style="text-align: center; font-size: 1.4em;">認證碼</div>
<div class="card-body">
    <form method="post" id="confirm-form" action="confirm.php">
        <div class="form-group">
            <label >已發送認證碼至您的信箱</label>
            <input class="form-control" name='confirmation' type="text" aria-describedby="accountHelp" placeholder="請輸入認證碼">
        </div>

        <!-- Fill by JS -->
        <input type='hidden' id="confirm-account" name='account' value="">

        <!-- Show Alert message if any error happended in AJAX -->
        <div class="alert alert-danger" id="alert-message" style="display: none;">
            <strong>Error: </strong> <span id="response"> Indicates a dangerous or potentially negative action. </span>
        </div>

        <button type="submit" id="submit-btn" class="btn btn-primary btn-block">確認</button>
    </form>

    <!-- Change to another content -->
    <div class="text-center" style="margin-top: 10px;">
        <a class="d-block small mt-3" onclick="LoadRegister()" style="margin: 0px 10px;">註冊帳號</a>
        <a class="d-block small" href="forgot-password.html" style="margin: 0px 10px;">忘記密碼?</a>
    </div>
</div>
<!-- /.card-body -->

<!-- No redirect, so send form by using AJAX -->

<script type="text/javascript">
    
    // Set the account value from global var(generated when registering)
    $("#confirm-account").attr("value", confirmAccount);

    // Invoke function when submit
    $("#confirm-form").submit(function(e) {

        // Make sure that btn can't be click again and alert is hided
        $("#submit-btn").addClass("disabled")
        $("#alert-message").fadeOut(200); 

        var url = "confirm.php"; // the script where you handle the form input.

        $.ajax({
               type: "POST",
               url: url,
               data: $("#confirm-form").serialize(), // serializes the form's elements.
               success: function(data)
               {
                    // If register succeed, load confirm content to popup (popup.html)
                    if(data == "succeed")
                    {
                        LoadLogin();
                    }
                    // Else show alert
                    else
                    {
                        $("#response").html(data); // show response from the php script. used for error
                        $("#alert-message").fadeIn(550);
                    }   
               }
             });

        e.preventDefault(); // avoid to execute the actual submit of the form.
    });
</script>