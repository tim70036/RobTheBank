const fugleRealtime = require('fugle-realtime');
const fetch = require('node-fetch');
const credential = require('./credential');
const mysql = require('mysql');

/* Fugle API */
const { api, socket } = fugleRealtime({
    version: 'latest', 
	token: credential.token, 
	socketIo: false, 
	fetch: fetch, 
});

/* DB connection */
const connection = mysql.createConnection({
    host: credential.dbhost,
    user: credential.dbuser,
    password: credential.dbpassword,
    database: credential.dbname 
});

/* Read vacation data */
const fs = require('fs');
let rawData = fs.readFileSync('data/vacation.json'); 


var vacationData,
    asyncDone,
    allAsyncNum,
    records,
    minDate,
    stockData,
    stockSkipData,
    stockDataIndex;




vacationData = JSON.parse(rawData);
//console.log(vacationData);

/* Used for async function */
asyncDone = 0;
allAsyncNum = 0;

/* Global arr for stock records */
records = [];


/* The most past data -> 2017/11/29, no data before this day */
//api.meta({ symbolId: "1470" , date: "20180614"}).then(console.log);
minDate = new Date("2017-11-28T00:00:00Z");

/* Process all stock */
stockSkipData = JSON.parse(fs.readFileSync('data/skip.json'));

/* Read stock data */
stockData = JSON.parse(fs.readFileSync('data/stockListSEM.json'));
stockSkipData = stockSkipData["SEM"];
// stockData = JSON.parse(fs.readFileSync('data/stockListOTC.json'));
// stockSkipData = stockSkipData["OTC"];

stockDataIndex = (process.argv[2] !== undefined) ? parseInt(process.argv[2]) : 0; 
// SEM skip :  93 94 136 183 440 452 453 498 506 546 577 640 668 677 729 735 746 760 802 822 824 826 872 889 896 897 899
// OTC skip :  18 27 48 55 72 74 78 85 123 161 191 204 211 232 250 256 277 280 281 282 285 287 311 330 367 372 374 389 407 413 415 416 424 430 435 442 453 459 464 465 467 480 482 499 501 516 530 531 543 551 554 576 591 611 614 618 620 622 630 633 634 635 640 655 661 669 696 719 734 735 736 753
RequestStockData(stockData[stockDataIndex]["symbol"].toString());

function RequestStockData(symbol)
{

    var i,
        j,
        oneDayTime,
        dateArr,
        tarDate,
        weekDay,
        dateStr,
        curYearVacations;
        

    /* Reset for each stock request */
    records = [];
    asyncDone = 0;
    allAsyncDone = 0;

    /* Making valid date array, start from yesterday to minDate */
    oneDayTime = 86400 * 1000;
    dateArr = [];
    for(i=1 ; ; i++)
    {
        tarDate = new Date(Date.now() - oneDayTime*i);

        /* Stop at minDate, there is no data before this day */
        if(tarDate < minDate)
            break;


        /* Skip Saturday and Sunday */
        weekDay = tarDate.getDay();
        if(weekDay === 0 || weekDay === 6)
            continue;

        /* Skip vacation */
        dateStr = DateToStr(tarDate);
        curYearVacations = vacationData[(tarDate.getFullYear()).toString()]; // get all vacations date string in this year 
        if(curYearVacations.indexOf(dateStr) != -1) // if dateStr exist in curYearVacation, skip it
            continue;

        /* This date is a valid trading day */
        dateArr.push(dateStr);        
    }

    console.log("=============================");
    console.log(stockDataIndex + "/" + (stockData.length-1) +" of all stocks...");
    console.log("Processing " + symbol + " from " + dateArr[dateArr.length-1] + " ~ " + dateArr[0]);
    console.log("Total " + dateArr.length + " days will be processed...");

    /* Number of all async api calls <- we will have a async api call for each day */
    allAsyncNum = dateArr.length;

    /* Call api for each day */
    for(j=0 ; j<dateArr.length ; j++)
    {
            api.meta({ symbolId: symbol , date: dateArr[i]}).then(ProcessDailyData);   
    }
}

/* Call back that deal with stock data */
function ProcessDailyData(obj){

    //console.log(obj);

    /* Convert current day to date obj */
    var Yr = obj['date'] / 10000 ;
    var Mn = (obj['date'] % 10000) / 100;
    var Dy = obj['date'] % 100 ;
    var startTime = new Date(Date.UTC(Yr, Mn-1, Dy));
    //console.log('Current day : ' + obj['date']);

    var symbol = obj['symbol']['id'];

    /* Invalid data, undefined and 0 are invalid */
    if(!obj['price'] || !obj['price']['close'] || !obj['price']['open'] || !obj['price']['highest'] || !obj['price']['lowest'] || !obj['volume'] || !obj['volume']['total'])
    {
        /* Do nothing */
    }
    /* Valid data */
    else
    {
        /* Gather data into global array */
        var timestamp = startTime.getTime() / 1000;
        var close = obj['price']['close'];
        var open = obj['price']['open'];
        var high = obj['price']['highest'];
        var low = obj['price']['lowest'];
        var vol = obj['volume']['total'];
        records.push([timestamp, close, open, high, low, vol]);
    }
    

    /* One async call back is done */ 
    asyncDone = asyncDone+1;

    /* If all async is done , insert data to DB */
    if(asyncDone >= allAsyncNum)
    {
        console.log("\n");
        console.log("All " + asyncDone + " records is received : ");
        console.log(records);

        /* Put all records into DB */
        console.log("\nStart inserting records...");
        InsertRecords(symbol, records, RequestStockData);
    }



}

function InsertRecords(symbol, records, callback)
{
    /* Ready to insert */
    var tableName = symbol + "_Day";
    console.log("\n");
    console.log("Now searching table " + tableName);

    var sql = "CREATE TABLE IF NOT EXISTS `RobTheBank`.`" + tableName + "` (\
              `id` MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,\
              `timestamp` INT UNSIGNED NULL,\
              `close` SMALLINT(6) UNSIGNED NULL,\
              `open` SMALLINT(6) UNSIGNED NULL,\
              `high` SMALLINT(6) UNSIGNED NULL,\
              `low` SMALLINT(6) UNSIGNED NULL,\
              `volume` INT UNSIGNED NULL,\
              PRIMARY KEY (`id`),\
              UNIQUE INDEX `timestamp_UNIQUE` (`timestamp` ASC));\
            ";
    
    /* Create table if not exist */
    connection.query(sql, function (err, result) {
        if (err) throw err;
        console.log("\n")
        console.log("Result of create " + tableName + " table if not exist : ")
        console.log(result);

        /* Start inserting */
        var sql = "INSERT IGNORE INTO `" + tableName + "` (timestamp, close, open, high, low, volume) VALUES ?";
        connection.query(sql, [records], function (err, result) {
            if (err) throw err;
            console.log("\n");
            console.log("Finish insert...")
            console.log("Number of records inserted: " + result.affectedRows);
            console.log("Finish processing for " + symbol + "...");
            console.log("=============================\n\n\n");

            /* Do next stock */
            stockDataIndex++;

            /* Skip invalid stock */
            while(stockSkipData.indexOf(stockDataIndex) > -1 )
            {
                console.log("Skiping " + stockDataIndex + " , not a valid stock in fugle...");
                stockDataIndex++;
            }
            if(stockDataIndex<stockData.length)
            {
                callback(stockData[stockDataIndex]["symbol"].toString())
            }
            

        });
    });

}

/* Convert date obj to fugle desired format */
function DateToStr(tarDate)
{

    var Yr = (tarDate.getFullYear()).toString();
    var Mn = (tarDate.getMonth()+1 < 10) ? "0" + (tarDate.getMonth()+1).toString() : (tarDate.getMonth()+1).toString();
    var Dy = (tarDate.getDate()  < 10) ? "0" + (tarDate.getDate()).toString()  : (tarDate.getDate()).toString();
    return  Yr + Mn + Dy;
}