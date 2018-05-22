<?php
    /* Determine load which content by query value */
    $loadMethod = "LoadLogin()";

    if(isset($_GET["page"]))
    {
        if($_GET["page"] === "register")
            $loadMethod = "LoadRegister()";
        else
            $loadMethod = "LoadLogin()";
    }

?>

<div class="card mx-auto mt-5 card-login">
    <!-- Page content will be load inside card-content -->
    <div class="card-content">
        
    </div>
    <!-- /.card-content -->


    <script type="text/javascript">

    /* Global var */
    var fadeDelay = 200;
    var confirmAccount = ''; // used for confirming account(set in register, use in confirm)

    /* Load the html content from server with animation */
    /* First fade out the card, and load the content, lastly, fade the card in */
    /* Use call back to prevent asynchronous */
    function LoadRegister(){
        $('.card').fadeOut(fadeDelay, function(){
                $('.card-content').load('register.html', function(){
                    $('.card').fadeIn(fadeDelay);
                });
        });
    }

    function LoadConfirm(){
        $('.card').fadeOut(fadeDelay, function(){
                $('.card-content').load('confirm.html', function(){
                    $('.card').fadeIn(fadeDelay);
                });
        });
    }

    function LoadLogin(){
        $('.card').fadeOut(fadeDelay, function(){
                $('.card-content').load('login.html', function(){
                    $('.card').fadeIn(fadeDelay);
                });
        });
    }


    /* Load page by checking demanding page in url query */
    <?php echo $loadMethod; ?>
    
    
    </script>
</div>
