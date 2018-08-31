const fugleRealtime = require('fugle-realtime');
const fetch = require('node-fetch');
const credential = require('./credential');
const mysql = require('mysql');

const { api, socket } = fugleRealtime({
    version: 'latest', 
	token: credential.token, 
	socketIo: false, 
	fetch: fetch, 
});

const connection = mysql.createConnection({
    host: credential.dbhost,
    user: credential.dbuser,
    password: credential.dbpassword,
    database: credential.dbname 
});

const fs = require('fs');

var tarDateStr,
    stockId;

/* Read data from args */
stockId = (process.argv[2] !== undefined) ? parseInt(process.argv[2]) : 0; 
tarDateStr = (process.argv[3] !== undefined) ? process.argv[3] : DateToStr(new Date(Date.now())); ;

/* Request tick */
api.tick({ symbolId: stockId.toString() , date: tarDateStr}).then(ProcessTickData);


/* Call back that deal with stock data */
function ProcessTickData(obj){

    /* Convert current day to date obj */
    var Yr = obj['date'] / 10000,
        Mn = (obj['date'] % 10000) / 100,
        Dy = obj['date'] % 100,
        date = new Date(Date.UTC(Yr, Mn-1, Dy));
    //console.log('Current day : ' + date);

    //console.log(obj);

    var time = (function(){
        /* Create a start time for session */
        var startTime = new Date(date);
        startTime.setUTCHours(1);
        startTime.setUTCMinutes(0);
        startTime.setUTCSeconds(0);
        startTime.setUTCMilliseconds(0);
        //console.log('session start : ' + startTime);

        /* Create an end time for session */
        var endTime = new Date(date);
        endTime.setUTCHours(5);
        endTime.setUTCMinutes(30);
        endTime.setUTCSeconds(0);
        endTime.setUTCMilliseconds(0);
        //console.log('session end : ' + endTime);

        return {
            start: startTime,
            end: endTime
        }
    })();

    var records = [],
        record;

    var tmpClose = -1,
        tmpOpen = -1,
        tmpHigh = -1,
        tmpLow = 999999,
        tmpVol = 0,
        prePrice = 0;

    var curTickTime,
        curTime = new Date(time.start),
        nextTime = new Date(time.start);

    nextTime.setUTCMinutes(nextTime.getUTCMinutes()+1);
    
    var first = true,    // used for checking if it is the first tick data (special case of out of interval)
        hasData = false; // used for checking if there is any data not yet been output after the for loop

    /* Tick data */
    var ticks = obj['ticks'];
    
    /* For each tick's data */
    for(var i=0 ; i<ticks.length ; i++)
    {
        var valid = true;
        /* Ignore trial */
        for(var k=0 ; k < ticks[i]['status'].length ; k++)
            if(ticks[i]['status'][k] === 'trial')
                valid = false;

        if(valid)
        {
            //console.log(ticks[i]);

            /* Get time */
            curTickTime = new Date(ticks[i]['time']);

            /* First, decide whether it is time to output previous data */
            /* Out of current interval special case: adjust first interval to a valid interval and don't output */
            if(first)
            {
                first = false;
                while(curTickTime >= nextTime)
                {
                    curTime.setUTCMinutes(curTime.getUTCMinutes()+1);
                    nextTime.setUTCMinutes(nextTime.getUTCMinutes()+1);
                }

            }
            /* Out of current interval general case: collect previous data */
            else if(curTickTime >= nextTime)
            {

                /* Summarize data */
                tmpClose = prePrice;

                // console.log('Cur tick : ' + curTickTime + ' out of interval');
                // console.log('Interval : ' + curTime + ' ~ ' + nextTime);
                // console.log('Close : ' + tmpClose);
                // console.log('Open : ' + tmpOpen);
                // console.log('High : ' + tmpHigh);
                // console.log('Low : ' + tmpLow);
                // console.log('Vol : ' + tmpVol);
                // console.log('Timestamp : ' + curTime.getTime()/1000);
                records.push([curTime.getTime()/1000, tmpClose, tmpOpen, tmpHigh, tmpLow, tmpVol]);

                /* Reset */
                tmpClose = -1;
                tmpOpen = -1;
                tmpHigh = -1;
                tmpLow = 999999;
                tmpVol = 0;
                hasData = false; // All data are outputed

                /* Adjuest interval to a valid interval */
                while(curTickTime >= nextTime)
                {
                    curTime.setUTCMinutes(curTime.getUTCMinutes()+1);
                    nextTime.setUTCMinutes(nextTime.getUTCMinutes()+1);
                }

                /* Check if next interval is valid */
                if(curTime > time.end)
                {
                    break;
                }
            }
            
            /* Second, start processing this tick */
            /* In current interval */
            if(curTickTime >= curTime && curTickTime < nextTime)
            {

                var curTickPrice = ticks[i]['value'][0];
                var curTickVol = ticks[i]['value'][1];

                if(tmpOpen === -1) 
                    tmpOpen = curTickPrice;

                if(curTickPrice > tmpHigh)
                    tmpHigh = curTickPrice;
                if(curTickPrice < tmpLow)
                    tmpLow = curTickPrice;

                tmpVol += curTickVol;
                prePrice = curTickPrice;

                /* Now we are accumulating data */
                hasData = true;
            }
            /* curTickTime < curTime */
            else
            {
                // ????? No such case
            }

            //console.log(ticks[i]);
        }
    }

    /* Now we check whether there is any data not yet output */
    /* The last data might be 13:30, but there is no chance to output this data*/
    if(hasData)
    {
         /* Summarize data */
        tmpClose = prePrice;

        // console.log('Cur tick : ' + curTickTime + ' out of interval');
        // console.log('Interval : ' + curTime + ' ~ ' + nextTime);
        // console.log('Close : ' + tmpClose);
        // console.log('Open : ' + tmpOpen);
        // console.log('High : ' + tmpHigh);
        // console.log('Low : ' + tmpLow);
        // console.log('Vol : ' + tmpVol);
        // console.log('Timestamp : ' + curTime.getTime()/1000);
        records.push([curTime.getTime()/1000, tmpClose, tmpOpen, tmpHigh, tmpLow, tmpVol]);
    }


    console.log("\n");
    console.log("Receive " + records.length + " records.");
    //console.log(records);

    /* Receive at least 1 record */
    if(records.length > 0)
    {
        console.log("\nStart inserting records...");
        InsertRecords(obj['symbol']['id'], records);
    }
    /* If empty, return control to main process */
    else
    {
        console.log("\nNo record to be inserted...");
        process.exit(0);
    }
}

function InsertRecords(symbol, records)
{   
    /* Ready to insert */

    var tableName = symbol + "_Min";

    console.log("\n");
    console.log("Now searching table " + tableName + "...");

    var sql = "CREATE TABLE IF NOT EXISTS `RobTheBank`.`" + tableName + "` (\
              `id` MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,\
              `timestamp` INT UNSIGNED NULL,\
              `close` FLOAT UNSIGNED NULL,\
              `open` FLOAT UNSIGNED NULL,\
              `high` FLOAT UNSIGNED NULL,\
              `low` FLOAT UNSIGNED NULL,\
              `volume` INT UNSIGNED NULL,\
              PRIMARY KEY (`id`),\
              UNIQUE INDEX `timestamp_UNIQUE` (`timestamp` ASC));\
            ";
    
    /* Create table if not exist */
    connection.query(sql, function (err, result) {
        if (err) throw err;
        console.log("\n")
        console.log(tableName + " exists, start inserting records...")

        /* Start inserting */
        var sql = "INSERT IGNORE INTO `" + tableName + "` (timestamp, close, open, high, low, volume) VALUES ?";
        connection.query(sql, [records], function (err, result) {
            if (err) throw err;
            console.log("\n");
            console.log("Finish insert...")
            console.log("Number of records inserted: " + result.affectedRows);
            
            /* We are done for this stock, return control to main process */
            process.exit(0);
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