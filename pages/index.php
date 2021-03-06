<?php
# Index page -> no need to login
require_once('credentials.php');
require_once('../vendor/autoload.php');
require_once('AWSCognitoWrapper.php');

use AWSCognitoApp\AWSCognitoWrapper;

$wrapper = new AWSCognitoWrapper();
$wrapper->initialize();


# Print HTML content
require_once('html.php');
head($wrapper->isAuthenticated());
?>


<div class="jumbotron jumbotron-fluid" style="background: #ececec; width: 100%; border-radius: 20px;">
  <div class="container">

    <div class="row center" style="color: black; font-weight: 900;">
      
    </div>
    <!-- /.row -->

    <div class="row center">
      <a class="btn btn-warning btn-lg" href="recordAdd.php" style="margin: 0px 30px;"> 建立交易日誌 </a>
      <h4>或者</h4>
      <a class="btn btn-warning btn-lg" href="selfSelectedStock.php"style="margin: 0px 30px;"> 查看自選股 </a>
    </div>
    <!-- /.row -->

    <div class="row" style="margin-top: 30px;">
      <div class="col-lg-12">

        <!-- TradingView Widget BEGIN -->
        <div class="tradingview-widget-container">
          <div id="tradingview_7210c" style="height: 500px;"></div>
          <div class="tradingview-widget-copyright"><a href="https://tw.tradingview.com/symbols/DJ-DJI/" rel="noopener" target="_blank"><span class="blue-text">DJI 圖表</span></a>由TradingView提供</div>
          
          <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
          <script type="text/javascript">
          new TradingView.widget(
          {
          "autosize": true,
          "symbol": "DJ:DJI",
          "interval": "1",
          "timezone": "Asia/Taipei",
          "theme": "Light",
          "style": "3",
          "locale": "zh_TW",
          "toolbar_bg": "#f1f3f6",
          "enable_publishing": false,
          "allow_symbol_change": true,
          "news": [
            "headlines"
          ],
          "container_id": "tradingview_7210c"
        }
          );
          </script>
        </div>
        <!-- TradingView Widget END -->
      </div>
    </div>
    <!-- /.row -->

  </div>
  <!-- /.container -->
</div>
<!-- /.jumbortron -->



<?php
tail();
?>

