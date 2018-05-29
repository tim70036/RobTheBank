<?php
// Symbol search
// Request: GET /search?query=<query>&type=<type>&exchange=<exchange>&limit=<limit>

// query: string. Text typed by the user in the Symbol Search edit box
// type: string. One of the symbol types supported by your back-end
// exchange: string. One of the exchanges supported by your back-end
// limit: integer. The maximum number of symbols in a response


if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['symbol']))
{

$responseStr = <<<JSON
[
    {
        "symbol": "BTC",
        "full_name": "BTCE:BTCUSD",
        "description": "testing description",
        "exchange": "Maicoin",
        "ticker": "<symbol ticker name, optional>",
        "type": "stock",
        "has_empty_bars": "true"
    },
    {
        "symbol": "ETH",
        "full_name": "BTCE:ETHUSD",
        "description": "testing description",
        "exchange": "Maicoin",
        "ticker": "<symbol ticker name, optional>",
        "type": "stock",
        "has_empty_bars": "true"
    }
]
JSON;



	echo $responseStr;
}

?>