<?php
# Check login, if not, exit
require_once('authenticate.php');

# Print HTML content
require_once('html.php');
head(true);
?>

<!-- TradingView Widget BEGIN -->
<div class="tradingview-widget-container">
  <div id="tradingview_9c053"></div>
  <div class="tradingview-widget-copyright"><a href="https://tw.tradingview.com/symbols/DJ-DJI/" rel="noopener" target="_blank"><span class="blue-text">DJI 圖表</span></a>由TradingView提供</div>
  <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
  <script type="text/javascript">
  new TradingView.widget(
  {
  "width": 980,
  "height": 610,
  "symbol": "DJ:DJI",
  "interval": "1",
  "timezone": "Asia/Taipei",
  "theme": "Dark",
  "style": "1",
  "locale": "zh_TW",
  "toolbar_bg": "#f1f3f6",
  "enable_publishing": false,
  "allow_symbol_change": true,
  "news": [
    "headlines"
  ],
  "container_id": "tradingview_9c053"
}
  );
  </script>
</div>
<!-- TradingView Widget END -->


<?php
tail();
?>

