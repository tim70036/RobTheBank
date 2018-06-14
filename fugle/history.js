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
var vacationData = JSON.parse(rawData);
//console.log(vacationData);


/* The most past data -> 2017/11/29, no data before this day */
//api.meta({ symbolId: "1470" , date: "20180614"}).then(console.log);
var minDate = new Date("2017-11-29T00:00:00Z");


/* Used for async function */
var asyncDone = 0;
var allAsyncNum = 0;

/* Global arr for stock records */
var records = [];

/* Process all stock */
var stockData = JSON.parse(fs.readFileSync('data/stockList.json'));
var stockDataIndex = 914; // skip 93 94 136 183 440 452 453 498 506 546 577 640 668 677 729 735 746 760 802 822 824 826 872 889 896 897 899
requestStockData(stockData[stockDataIndex]["symbol"].toString());

function requestStockData(symbol)
{


    /* Reset for each stock request */
    records = [];
    asyncDone = 0;
    allAsyncDone = 0;

    /* Making valid date array, start from yesterday to minDate */
    var oneDayTime = 86400 * 1000;
    var dateArr = [];
    for(var i=1 ; ; i++)
    {
        var tarDate = new Date(Date.now() - oneDayTime*i);

        /* Stop at minDate, there is no data before this day */
        if(tarDate < minDate)
            break;


        /* Skip Saturday and Sunday */
        var weekDay = tarDate.getDay();
        if(weekDay === 0 || weekDay === 6)
            continue;

        /* Skip vacation */
        var dateStr = DateToStr(tarDate);
        var curYearVacations = vacationData[(tarDate.getFullYear()).toString()]; // get all vacations date string in this year 
        if(curYearVacations.indexOf(dateStr) != -1) // if dateStr exist in curYearVacation, skip it
            continue;

        /* This date is a valid trading day */
        dateArr.push(dateStr);        
    }

    console.log("=============================");
    console.log(stockDataIndex + "/" + (stockData.length-1) +" of stock data...");
    console.log("Processing " + symbol + " from " + dateArr[dateArr.length-1] + " ~ " + dateArr[0]);
    console.log("Total " + dateArr.length + " days will be processed...");

    /* Number of all async api calls <- we will have a async api call for each day */
    allAsyncNum = dateArr.length;

    /* Call api for each day */
    for(var i=0 ; i<dateArr.length ; i++)
    {
            

            // var errHandler = (function(){ return (function(e){ var a = dateArr[i]; console.log(a); }) })();
            // //console.log(errHandler);
            // errHandler(1);
            // // call back
            // api.meta({ symbolId: symbol , date: dateArr[i]}).then(processDailyData).catch(errHandler);

            api.meta({ symbolId: symbol , date: dateArr[i]}).then(processDailyData);
        
    }
}

/* Call back that deal with stock data */
function processDailyData(obj){

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
        InsertRecords(symbol, records, requestStockData);
    }



}

function InsertRecords(symbol, records, callback)
{
    /* Ready to insert */
    console.log("\n");
    console.log("Now searching table " + symbol);

    var sql = "CREATE TABLE IF NOT EXISTS `RobTheBank`.`" + symbol + "` (\
              `id` INT NOT NULL AUTO_INCREMENT,\
              `timestamp` INT NULL,\
              `close` INT NULL,\
              `open` INT NULL,\
              `high` INT NULL,\
              `low` INT NULL,\
              `volume` INT NULL,\
              PRIMARY KEY (`id`),\
              UNIQUE INDEX `timestamp_UNIQUE` (`timestamp` ASC));\
            ";
    
    /* Create table if not exist */
    connection.query(sql, function (err, result) {
        if (err) throw err;
        console.log("\n")
        console.log("Result of create " + symbol + " table if not exist : ")
        console.log(result);

        /* Start inserting */
        var sql = "INSERT IGNORE INTO `" + symbol + "` (timestamp, close, open, high, low, volume) VALUES ?";
        connection.query(sql, [records], function (err, result) {
            if (err) throw err;
            console.log("\n");
            console.log("Finish insert...")
            console.log("Number of records inserted: " + result.affectedRows);
            console.log("Finish processing for " + symbol + "...");
            console.log("=============================\n\n\n");

            /* Do next Stock */
            stockDataIndex++;
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