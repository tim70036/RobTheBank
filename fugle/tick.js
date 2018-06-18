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


var oneDayTime = 86400 * 1000;
var oneMinTime = 60 * 1000;
var numDay = 2;

var tarDate = new Date(Date.now() - oneDayTime*numDay);
var tarDateStr = DateToStr(tarDate);
console.log(tarDateStr);




api.tick({ symbolId: '3026' , date: tarDateStr}).then(processStock);

/* Call back that deal with stock data */
function processStock(obj){

    /* Convert current day to date obj */
    var Yr = obj['date'] / 10000 ;
    var Mn = (obj['date'] % 10000) / 100;
    var Dy = obj['date'] % 100 ;
    var date = new Date(Date.UTC(Yr, Mn-1, Dy));
    //console.log('Current day : ' + date);

    console.log(obj);

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

    var records = [];
    var record;

    var tmpClose = -1;
    var tmpOpen = -1;
    var tmpHigh = -1;
    var tmpLow = 999999;
    var tmpVol = 0;
    var prePrice = 0;

    var curTickTime;
    var curTime = new Date(time.start);
    var nextTime = new Date(time.start);
    nextTime.setUTCMinutes(nextTime.getUTCMinutes()+1);
    
    var first = true;    // used for checking if it is the first tick data (special case of out of interval)
    var hasData = false; // used for checking if there is any data not yet been output after the for loop

    return;
    /* Tick data */
    var ticks = obj['ticks'];

    
    
    /* For each tick's data */
    for(var i=0 ; i<ticks.length ; i++)
    {
        /* Ignore trial */
        if(!(ticks[i]['status'][0] === 'trial'))
        {
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
                    nextTime.setUTCMinutes(nextTIme.getUTCMinutes()+1);
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
                if(curTime > endTime)
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

    console.log(records);
    //InsertRecords(records);
}

function InsertRecords(records)
{   
    connection.connect(function(err) {
        if (err) throw err;
        console.log("Database Connected!");

        var sql = "INSERT INTO customers (name, address) VALUES ?";

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