<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Lin大帶你搶銀行</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../dist/css/main.css" rel="stylesheet">
    <link href="../dist/css/logo.css" rel="stylesheet">
    <link href="../dist/css/simple-sidebar.css" rel="stylesheet">
    <link href="../dist/css/popup-animate.css" rel="stylesheet">

    <!-- Magnific Popup core CSS file -->
    <link rel="stylesheet" href="../dist/css/magnific-popup.css">

    <!-- Morris Charts CSS -->
    <link href="../vendor/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <div id="wrapper" class="toggled">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0;">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <button type="button" class="btn btn-primary btn-sidebar btn-lg" id="menu-show">
                    <i class="fa  fa-navicon"></i>
                    選單
                </button>

                 <!-- Logo -->
                <a href="index.php" id="logo">Rob The Bank</a>
            </div>

           

            <!-- /.navbar-header -->

            <div id="sidebar-wrapper">
                <div class="navbar-default sidebar" role="navigation">
                    <div class="sidebar-nav navbar-collapse">
                        <ul class="nav" id="side-menu">
                            <li class="sidebar-header">
                                功能選單
                            </li>
                            <!-- 首頁 -->
                            <li>
                                <a id="main" class="sidebar-link"><i class="fa fa-home fa-fw"></i> 首頁 </a>
                            </li>
                            <!-- 帳號管理 -->
                            <li>
                                <a id="account" class="sidebar-link"><i class="fa fa-user fa-fw"></i> 帳號管理 </a>
                            </li>
                            <!-- 交易日誌 -->
                            <li>
                                <a href="#"><i class="fa fa-book fa-fw"></i> 交易日誌 <span class="fa arrow"></span></a>
                                <ul class="nav nav-second-level">
                                    <li>
                                        <a href="#" id="recordAdd" class="sidebar-link">建立日誌</a>
                                    </li>
                                    <li>
                                        <a href="#" id="recordHis" class="sidebar-link">歷史回顧</a>
                                    </li>
                                </ul>
                                <!-- /.nav-second-level -->
                            </li>
                            <!-- 自選股 -->
                            <li>
                                <a href="#"><i class="fa fa-sitemap fa-fw"></i> 自選股 <span class="fa arrow"></span></a>
                                <ul class="nav nav-second-level">
                                    <li>
                                        <a href="#" id="stockList" class="sidebar-link">自選股清單</a>
                                    </li>
                                    <li>
                                        <a href="#" id="stockSet" class="sidebar-link">設定</a>
                                    </li>
                                </ul>
                                <!-- /.nav-second-level -->
                            </li>

                            <li>
                                <a href="#menu-hide"  id="menu-hide">隱藏選單</a> 
                            </li>
                        </ul>
                        <!-- /.sidebar -->
                    </div>
                </div>
                <!-- /.sidebar-collapse -->
            </div>

            
            <div class="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class=""> <a class="navbar-btn popup-link" href="popup.php?page=register">註冊</a> </li>
                    <li class=""> <a class="navbar-btn popup-link" href="popup.php?page=login">登入</a> </li>
                    <!-- <li class="navbar-btn"> <a id="logout" href="#">Logout</a> </li> -->
                </ul>
            </div>
        </nav>
        <!-- /.navbar-static-side -->
<!-- -------------------------------------------------------------------------- -->
        <!-- Content Start -->
        <div id="page-content-wrapper">

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.js"></script>

    <!-- Magnific Popup core JS file -->
    <script src="../dist/js/jquery.magnific-popup.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- Side Bar  JavaScript -->
    <script>
    $("#menu-hide").click(function(e) {
        e.preventDefault();
        $("#wrapper").removeClass("toggled");
    });
    $("#menu-show").click(function(e) {
        e.preventDefault();
        $("#wrapper").addClass("toggled");
    });
    </script>

    <!-- Side Bar Link  JavaScript -->
    <script type="text/javascript">
    /* Load new page when user click */
    $(".sidebar-link").on("click", function(){
        $(this).blur();
        var tarElement = $(this);
        var requestTarget = $(this).attr("id")+".php" ;

        $.ajax({
        type: 'GET',
            url: requestTarget,
            // data: postedData,
            dataType: 'text',
            success: function(response){

                /* Removing active effect */
                $("ul li .active").removeClass('active');

                /* Load target page */
                $("#page-content-wrapper").html(response);

                /* Adding active effect */
                var parElement = tarElement.addClass('active').parent();
                while (true) {
                    if (parElement.is('li')) {
                        parElement = parElement.parent().addClass('in').parent();
                    } else {
                        break;
                    }
                }
            },
            fail: function(response){
                console.log(response);
            }
        });
    });
    /* Load main page when load index.html */
    $("#main").click()
    </script>


    <!-- Popup initialization  JavaScript -->
    <script type="text/javascript">
    $(document).ready(function() {
          $('.popup-link').magnificPopup({
                type: 'ajax',
                removalDelay: 500, // Delay in milliseconds before popup is removed
                mainClass: 'mfp-fade', // Assign animation class (loaded from popup-animate.css)
                cursor: 'mfp-ajax-cur', // CSS class that will be added to body during the loading (adds "progress" cursor)
                tError: '<a href="%url%">The content</a> could not be loaded.' //  Error message, can contain %curr% and %total% tags if gallery is enabled
            });
    });
    </script>

    <!-- Logout link  JavaScript -->
    <script type="text/javascript">
        $("#logout").on("click", function(){

            var Url = "memberControl.php"; // the script where you handle the form input.
            $.ajax({
                   type: "POST",
                   url: Url,
                   data: { 'ProcessType' : 'logout'},
                   success: function(data)
                   {
                        // If logout succeed, refesh page to index
                        if(data == "succeed")
                        {
                            window.location.replace('index.php');
                        }
                        // Else show alert
                        else
                        {
                            alert('logout failed');
                        }   
                   }
                 });

        });
    </script>

</body>

</html>
