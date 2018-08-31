const cp = require('child_process');
const fs = require('fs');

/* Set up data */
var stockData = JSON.parse(fs.readFileSync('data/stockList.json')), // Read all stock data to an array
    stockDataIndex = 0,
    tarDate = new Date(Date.now()), // The date we want to request
    tarDateStr = DateToStr(tarDate);

SingleRequest(stockData[stockDataIndex]['symbol'].toString())

/* Start */
function SingleRequest(symbol) {

    console.log('=============================');
    console.log(stockDataIndex + '/' + (stockData.length-1) +' of all stocks...');
    console.log('Processing ' + symbol + ' ...');

    /* Produce a child process */
    var cmd = 'node singleTick.js ' + symbol + ' ' + tarDateStr;
    var singleTick = cp.exec(cmd, {timeout : 15000}); // This child process will timeout after 15 sec
    console.log('\nCreate child process "' + cmd +  '"' );

    /* Log out process */
    singleTick.stdout.on('data', function (data) {

        console.log(data);
    });
    singleTick.stderr.on('data', function (data) {
        console.log(data);
    });
    
    /* Continue to do next stock, even if error */
    singleTick.on('exit', function (code) {

        console.log('child process exited with code ' + code);
        
        console.log('\nFinish processing for ' + symbol + '...');
        console.log('=============================\n\n');
        DoNextStock();
    });
}

function DoNextStock()
{
    /* Do next stock */
    stockDataIndex++;

    /* Continue only if we haven't done */
    if(stockDataIndex<stockData.length)
    {
        SingleRequest(stockData[stockDataIndex]['symbol'].toString());
    }
}

/* Convert date obj to fugle desired format */
function DateToStr(tarDate) {
    var Yr = (tarDate.getFullYear()).toString();
    var Mn = (tarDate.getMonth()+1 < 10) ? '0' + (tarDate.getMonth()+1).toString() : (tarDate.getMonth()+1).toString();
    var Dy = (tarDate.getDate()  < 10) ? '0' + (tarDate.getDate()).toString()  : (tarDate.getDate()).toString();
    return  Yr + Mn + Dy;
}