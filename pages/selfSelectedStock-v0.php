<?php
# Check login, if not, exit
require_once('authenticate.php');

# Print HTML content
require_once('html.php');
require_once('config.php');
head(true);
$userName = ($wrapper->getUser())['Username'];

?>
<head>

	<link rel="stylesheet" type="text/css" href="../dist/css/selfSelectedStock.css">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://www.datatables.net/rss.xml">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.5/css/select.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.2/css/responsive.dataTables.min.css">
	<link type="text/css" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.10/css/dataTables.checkboxes.css" rel="stylesheet">

	
	<script type="text/javascript" src="https://cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.6/js/dataTables.select.min.js"></script>
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.10/js/dataTables.checkboxes.min.js"></script>


	<script src="https://unpkg.com/socket.io-client/dist/socket.io.js"></script>
	<script src="https://unpkg.com/fugle-realtime@0.2.5/bundle/index.js"></script>
	<script type="text/javascript" src="stockSymbol.json"></script>

</head>

<?php 

					mysqli_select_db($connection,DB_DATABASE);
					$sql="SELECT DISTINCT stockId FROM UserGroup WHERE userName = '$userName'";
					$result = mysqli_query($connection,$sql);
					echo "<script>";
					echo "var stockList = [];";
					while($row = mysqli_fetch_array($result)) {
						if($row['stockId'] != "0") echo "stockList.push(\"" . $row['stockId'] . "\");";
					}
					//echo "console.log(stockList);";
					echo "</script>";
?>

<script>

Notification.requestPermission().then(function(result) {
});

var userName = "<?php echo $userName; ?>";
var table, remindTable;		
var stockSet = [], reminderSet = [], supResPrices = [];  


	/*$('#stock-select').keyup(function(){
			var txt = $(this).val();
			if(txt != ''){

			}else{
				$('#result').html('');
				$.ajax({
					url:"fetchStock.php",
					method:"post",
					data:{search:txt},
					dataType:"text",
					success:function(data){
						$('#stock-select-result').html(data);
					}

				});
			}
	});*/


	$(document).on('click', '#rename-group', function(){ 
		var stockGroup = $('#categories :selected').val();
		console.log(stockGroup);
		if(stockGroup === "請選自選組合"){
			alert("請選股票群組");
			return;
		}
		$("#rename-group-area").css({'display':'block'});

	});

	$(document).on('click', '#add-group', function(){
		$("#add-group-area").css({'display':'block'});
	});

	$(document).on('click', '#add-stock', function(){
		var stockGroup = $('#categories :selected').val();
		if(stockGroup === '請選自選組合'){
			alert("請選股票群組");
			return;
		}
		$("#add-stock-area").css({'display':'block'});
	});

	function showStock(){   
		var stockGroup = $('#categories :selected').val();
		//console.log(stockGroup);
		$.ajax({
			url:"stockGroup.php",
			method:"POST",
			data:{
				stockGroup:stockGroup,
				userName:userName
			},
			success:function(response){
				$("#stock-group-content").html(response);
			},
			fail: function(response){
                console.log(response);
            }

		})

	}

	$(document).on('click', '#delete-group', function(){ 
		var stockGroup = $('#categories :selected').val();
		var groupName = $('#categories :selected').text();
		if(stockGroup === '請選自選組合'){
			alert("請選股票群組");
			return;
		}

		var result = confirm("確定刪除群組 " + groupName + " 嗎？");

		if(result == true){
			$.ajax({
				url:"deleteGroup.php",
				method:"POST",
				//processData: false,
				data:{
					stockGroup:stockGroup,
					stockSymbol: stockList,
					userName:userName
				},
				success:function(response){
					location.reload();
					$("#stock-group-content").html(response);
				},
				fail: function(response){
	                console.log(response);
	            }

			});
		}else{
			return;
		}

	});

	$(document).on('click', '#edit-group', function(){ 
		var stockGroup = $('#categories :selected').val();
		if(stockGroup === '請選自選組合'){
			alert("請選股票群組");
			return;
		}

		if(table.column(0).visible()){
			table.column(0).visible(false);
			$("#delete-stock-submit").css({'display':'none'});
		}else{
			table.column(0).visible(true);
			$("#delete-stock-submit").css({'display':'block'});
		}

	});

	$(document).on('click', '#edit-reminder', function(){
		if(remindTable.column(0).visible()){
			remindTable.column(0).visible(false);
			$("#update-stock-reminder").css({'display':'none'});
			remindTable.ajax.reload();
		}else{
			remindTable.column(0).visible(true);
			$("#update-stock-reminder").css({'display':'block'});
		}

	});

	$(document).on('keyup','#stockName',function(){
		var searchField = $(this).val();
		if(searchField === ''){
			$('#addStock-filter-records').html('');
			return;
		}

		var regex = new RegExp(searchField, "i");

		var output = '<div class="row">';
            var count = 1;
			  $.each(stockDictionary, function(key, val){
				if ((val.symbol.toString().search(regex) != -1) || (val.name.search(regex) != -1)) {
				  output += '<div class="col-md-6 well add-stock-filter-result" id = ' + val.symbol + '>';
				  output += '<p>' + val.symbol + " " + val.name + " "  + val.market + " "  + val.industry + '</p>'
				  output += '</div>';
				  if(count%2 == 0){
					output += '</div><div class="row">'
				  }
				  count++;
				}
			  });
			  output += '</div>';
			  $('#addStock-filter-records').html(output);
	});

	$(document).on('click', '.add-stock-filter-result', function(){
		var stockSymbol = $(this).attr("id");
		var groupId = $('#categories :selected').val();

		$.ajax({
			url:"addStockToGroup.php",
			method:"POST",
			data:{
				stockSymbol:stockSymbol,
				groupId:groupId,
				userName:userName
			},
			success:function(response){

				location.reload();
				$("#stock-group-content").html(response);
			},
			fail: function(response){
                console.log("fail:"+response);
            }

		})
	});

	$(document).on('click', '#delete-stock-submit', function(){

		var groupId = $('#categories :selected').val();
		var groupName = $('#categories :selected').text();
		var t = [];
		var rows_selected = table.column(0).$('tr.selected');
		//console.log(rows_selected);
		$.each(rows_selected, function(index){

			t.push(rows_selected[index].children[1].innerText);

		});

		var result = confirm("確定從群組 " + groupName + " 刪除 " + t + " 嗎？");

		if(result == true){
			$.ajax({
				url:"deleteStockFromGroup.php",
				method:"POST",
				data:{
					stockSymbol:t,
					groupId:groupId,
					userName:userName
				},
				success:function(response){

					location.reload();
					$("#stock-group-content").html(response);
				},
				fail: function(response){
	                console.log("fail:"+response);
	            }

			});
		}else{
			return;
		}
		
		
	});

	$(document).on('click', '#update-stock-reminder', function(){


		var t=[], element=[];
		var rows_selected = remindTable.column(0).$('tr.selected');
		$.each(rows_selected, function(index){

			var stockId = rows_selected[index].childNodes[1].childNodes[0].data;
			var sup1 = rows_selected[index].childNodes[5].childNodes[0].children[0].value;
			var sup2 = rows_selected[index].childNodes[6].childNodes[0].children[0].value;
			var sup3 = rows_selected[index].childNodes[7].childNodes[0].children[0].value;
			var res1 = rows_selected[index].childNodes[8].childNodes[0].children[0].value;
			var res2 = rows_selected[index].childNodes[9].childNodes[0].children[0].value;
			var res3 = rows_selected[index].childNodes[10].childNodes[0].children[0].value;
			if($.isNumeric(sup1) && $.isNumeric(sup2) && $.isNumeric(sup3) && $.isNumeric(res1) && $.isNumeric(res2) && $.isNumeric(res3)){
				element = [stockId,
							sup1,
							sup2,
							sup3,
							res1,
							res2,
							res3];
				t.push(element);
			}else{
				alert("請輸入正確數字");
				return;
			}
		});

		$.ajax({
			url:"updateReminder.php",
			method:"POST",
			data:{
				data: t,
				userName:userName
			},
			success:function(response){
				location.reload();
				$("#stock-group-content").html(response);
			},
			fail: function(response){
                console.log("fail:"+response);
            }

		})
	})


	$(document).on('click', '.reminder-select-checkbox',function(e){

		var data = remindTable.row( $(this).parents('tr') ).data();

		if($(this).parents('tr').hasClass("selected")){
			
			var sup1Html = "<div class=\"reminder-input-field\" ><input class=\" form-control\" id=\"input-sup1\" type=\"text\"></div>";
			var sup2Html = "<div class=\"reminder-input-field\" ><input class=\" form-control\" id=\"input-sup2\" type=\"text\"></div>";
			var sup3Html = "<div class=\"reminder-input-field\" ><input class=\" form-control\" id=\"input-sup3\" type=\"text\"></div>";
			var res1Html = "<div class=\"reminder-input-field\" ><input class=\" form-control\" id=\"input-res1\" type=\"text\"></div>";
			var res2Html = "<div class=\"reminder-input-field\" ><input class=\" form-control\" id=\"input-res2\" type=\"text\"></div>";
			var res3Html = "<div class=\"reminder-input-field\" ><input class=\" form-control\" id=\"input-res3\" type=\"text\"></div>";
			$(this).parents('tr').children(".reminder-area").html('');
			$(sup1Html).appendTo($(this).parents('tr').children(".reminder-sup1"));
			$(sup2Html).appendTo($(this).parents('tr').children(".reminder-sup2"));
			$(sup3Html).appendTo($(this).parents('tr').children(".reminder-sup3"));
			$(res1Html).appendTo($(this).parents('tr').children(".reminder-res1"));
			$(res2Html).appendTo($(this).parents('tr').children(".reminder-res2"));
			$(res3Html).appendTo($(this).parents('tr').children(".reminder-res3"));
			$(this).parents('tr').children(".reminder-sup1").children("div").children("input").val(data[5]);
			$(this).parents('tr').children(".reminder-sup2").children("div").children("input").val(data[6]);
			$(this).parents('tr').children(".reminder-sup3").children("div").children("input").val(data[7]);
			$(this).parents('tr').children(".reminder-res1").children("div").children("input").val(data[8]);
			$(this).parents('tr').children(".reminder-res2").children("div").children("input").val(data[9]);
			$(this).parents('tr').children(".reminder-res3").children("div").children("input").val(data[10]);
		}else{
			$(this).parents('tr').children(".reminder-area").html('');
			$(this).parents('tr').children(".reminder-sup1").text(data[5]);
			$(this).parents('tr').children(".reminder-sup2").text(data[6]);
			$(this).parents('tr').children(".reminder-sup3").text(data[7]);
			$(this).parents('tr').children(".reminder-res1").text(data[8]);
			$(this).parents('tr').children(".reminder-res2").text(data[9]);
			$(this).parents('tr').children(".reminder-res3").text(data[10]);
		}

		//console.log(data);
	});

	function getReminderTable(){

		$.ajax({
	    	
	    	url: "getStockReminder.php",
	    	type: "POST",
	    	data:{
	    		"userName":userName
	    	},
	    	dataType: "text",
	    	success:function(data){

	    		supResPrices = JSON.parse(data);
	    		refreshStockData();
	    	},
	    	fail:function(response){
	    		console.log("fail" + response);
	    	}
    	});

	}


	

	
	


	function addGroup(){
		var groupName = $('#groupName').val();
		$.ajax({
			url:"addGroup.php",
			method:"POST",
			data:{
				groupName:groupName,
				userName:userName
			},
			success:function(response){
				location.reload();
				$("#stock-group-content").html(response);
			},
			fail: function(response){
                console.log("fail:"+response);
            }

		})

	}

	function renameGroup(){
		var groupId = $('#categories :selected').val();
		var newGroupName = $('#rename-group-text').val();

		$.ajax({
			url:"renameGroup.php",
			method:"POST",
			data:{
				groupId:groupId,
				newGroupName:newGroupName,
				userName:userName
			},
			success:function(response){
				location.reload();
				$("#stock-group-content").html(response);
			},
			fail: function(response){
                console.log("fail:"+response);
            }

		})
	}


	


	function cancelAddGroup(){
		$("#add-group-area").css({'display':'none'});
	}

	function cancelRenameGroup(){
		$("#rename-group-text").val("");
		$("#rename-group-area").css({'display':'none'});
	}

	function cancelAddStock(){
		$("#stockName").val("");
		$('#addStock-filter-records').html('');
		$("#add-stock-area").css({'display':'none'});
	}

</script>

<script>
	
	
    

	$(document).ready( function () {


    	table  = $('#stock-table').DataTable({
    		"ajax": function (data, callback1, settings) {
       			 callback1( { data: stockSet } );
    		},

    		"createdRow": function ( row, data, index ) {
    			$(row).find('.stock-symbol').css('color', '#00ccff');

    			if(data[5][0] != ""){
    				var num = Number(data[5].substring(0, data[5].length-1));
		            if ( num > 0 ) {
		                $(row).find('.stock-percent').css('color', 'red');
		            }else if(num < 0){
		            	$(row).find('.stock-percent').css('color', 'green');
		            }else{
		            	$(row).find('.stock-percent').css('color', 'black');
		            }
    			}
    			
        	},
    		columns:[
    			{},
    			{ title: "名稱" },
            	{ title: "收盤" },
            	{ title: "買進" },
            	{ title: "賣出" },
            	{ title: "漲跌" },
            	{ title: "張數" },
            	{ title: "平盤" },
	            { title: "開盤" },
	            { title: "最高" },
	            { title: "最低" }
    		],
    		columnDefs: [ {
    			visible: false,
	            orderable: false,
	            className: 'select-checkbox',
	            targets:   0,
	        },{
	        	className: 'stock-symbol stockTable-area',
	        	defualtContent:"",
	        	targets: 1
	        },{
	        	className: 'stock-close stockTable-area',
	        	defualtContent:"",
	        	targets: 2
	        },{
	        	className: 'stock-buy5 stockTable-area',
	        	defualtContent:"",
	        	targets: 3
	        },{
	        	className: 'stock-sell5 stockTable-area',
	        	defualtContent:"",
	        	targets: 4
	        },{
	        	className: 'stock-percent stockTable-area',
	        	defualtContent:"",
	        	targets: 5
	        },{
	        	className: 'stock-volume stockTable-area',
	        	defualtContent:"",
	        	targets: 6
	        },{
	        	className: 'stock-ref stockTable-area',
	        	defualtContent:"",
	        	targets: 7
	        },{
	        	className: 'stock-open stockTable-area',
	        	defualtContent:"",
	        	targets: 8
	        },{
	        	className: 'stock-low stockTable-area',
	        	defualtContent:"",
	        	targets: 9
	        }],
	        select: {
	            style:    'multi',
	            selector: 'td:first-child'
	        },
	        order: [[ 1, 'asc' ]],
    		responsive: true,
        	dom: 'Bf<"#rename-group-wrapper.input-field"><"#add-group-wrapper.input-field"><"#add-stock-wrapper.input-field">rt<"stock-bottom-buttons">ip',
        	buttons: [
            
            {
            	text: '更新名稱',
            	attr: {
                	id: 'rename-group'
                },
                className:"btn btn-secondary"
            },
            {
            	text: '編輯追蹤',
            	attr: {
                	id: 'edit-group'
                },
                className:"btn btn-secondary"
            },
            {
            	text: '新增追蹤股票',
            	attr: {
                	id: 'add-stock'
                },
                className:"btn btn-secondary"
            },
            {
            	text: '新增組合',
            	attr: {
                	id: 'add-group'
                },
                className:"btn btn-secondary"
            },{
                text: '刪除組合',
                attr: {
                	id: 'delete-group'
                },
                className:"btn btn-secondary"
                
            }
        ],
        

	});

	new $.fn.dataTable.Buttons( table, {
    	name: 'submit',
    	buttons: [
    		{
    			text: '刪除股票',
    			attr: {
    				id: 'delete-stock-submit'
    			},
                className:"btn btn-secondary"
    		}
    	]
	});

	

    table.buttons(1,null).containers().appendTo($(".stock-bottom-buttons"));
    $("#delete-stock-submit").css({'display':'none'});


		$("#rename-group-wrapper").html('\
							<div id = "rename-group-area" class="rename-group-area" style="display:none;">\
								<div class="input-group input-group-sm input-group-bar col-xs-4 col-sm-4 col-md-4 col-lg-3">\
											<input class="form-control" id = "rename-group-text" type="text" placeholder="更改名稱">\
											<span class="input-group-btn">\
												<button type="button" class="btn btn-secondary btn-rename" onclick="renameGroup()">確認</button>\
											</span>\
											<span class="input-group-btn" onclick="cancelRenameGroup()">\
												<button class="btn btn-secondary">取消</button>\
											</span>\
								</div>\
							</div>');
		$("#add-group-wrapper").html('\
							<div id="add-group-area" class="add-group-area" style="display:none">\
								<div class="input-group input-group-sm input-group-bar col-xs-4 col-sm-4 col-md-4 col-lg-3">\
											<input class="form-control" id="groupName" autocomplete="off" placeholder="新增組合名稱" type="text">\
											<span class="input-group-btn">\
												<button type="button" class="btn btn-secondary" onclick="addGroup()">新增</button>\
											</span>\
											<span class="input-group-btn" >\
												<button type="button" class="btn btn-secondary" onclick="cancelAddGroup()">取消</button>\
											</span>\
								</div>\
							</div>');
		
		$("#add-stock-wrapper").html('\
							<div id="add-stock-area" class="add-stock-area" style="display:none">\
									<div class="input-group input-group-sm input-group-bar col-xs-4 col-sm-4 col-md-4 col-lg-3">\
										<input  type="text" class="form-control" id="stockName" autocomplete="off" placeholder="新增股票名稱">\
										<span class="input-group-btn">\
											<button type="button" class="btn btn-secondary" onclick="cancelAddStock()">取消</button>\
										</span>\
									</div>\
							</div>\
							<div id = "addStock-filter-records"></div>');	

		remindTable = $('#reminder-table').DataTable({


    		"ajax": function (data, callback2, settings) {
       			 callback2( { data: reminderSet } );
    		},

    		"createdRow": function ( row, data, index ) {
    			$(row).find('.stock-symbol').css('color', '#00ccff');
        	},

    		columnDefs: [ {
    			visible: false,
	            orderable: false,
	            className: 'select-checkbox reminder-select-checkbox',
	            targets:   0,
	        },{
	        	className: 'stock-symbol',
	        	defualtContent:"",
	        	targets: 1
	        },{
	        	className: 'stock-close',
	        	defualtContent:"",
	        	targets: 2
	        },{
	        	className: 'stock-buy5',
	        	defualtContent:"",
	        	targets: 3
	        },{
	        	className: 'stock-sell5',
	        	defualtContent:"",
	        	targets: 4
	        },{
	        	className: 'reminder-sup1 reminder-area',
	        	defualtContent:"",
	        	
	        	targets: 5
	        },{
	        	className: 'reminder-sup2 reminder-area',
	        	defualtContent:"",
	        	targets: 6
	        },{
	        	className: 'reminder-sup3 reminder-area',
	        	defualtContent:"",
	        	targets: 7
	        },{
	        	className: 'reminder-res1 reminder-area',
	        	defualtContent:"",
	        	targets: 8
	        },{
	        	className: 'reminder-res2 reminder-area',
	        	defualtContent:"",
	        	targets: 9
	        },{
	        	className: 'reminder-res3 reminder-area',
	        	defualtContent:"",
	        	targets: 10
	        }],
	        select: {
	            style:    'multi',
	            selector: '.reminder-select-checkbox'
	        },
	        order: [[ 1, 'asc' ]],
    		columns:[
    			{
    				//"class": "update-control"
    			},
    			{ "title": "名稱" },
            	{ "title": "收盤" },
            	{ "title": "買進" },
            	{ "title": "賣出" },
            	{ "title": "支撐1" },
            	{ "title": "支撐2" },
            	{ "title": "支撐3" },
	            { "title": "壓力1" },
	            { "title": "壓力2" },
	            { "title": "壓力3" }


    		],
    		responsive: true,
		
        	dom: 'Bfrt<"reminder-bottom-buttons">ip',
        	buttons: [
	            {
	            	text: '編輯到價提醒',
	            	attr: {
	                	id: 'edit-reminder'
	                },
                className:"btn btn-secondary"
            	},
	            
            ]
        	
        

	});

	new $.fn.dataTable.Buttons( remindTable, {
    	name: 'submit-reminder',
    	buttons: [
    		{
    			text: '更新提醒',
    			attr: {
    				id: 'update-stock-reminder'
    			},
                className:"btn btn-secondary"
    		}
    	]
	});

	remindTable.buttons(1,null).containers().appendTo($(".reminder-bottom-buttons"));
	$("#update-stock-reminder").css({'display':'none'});
	getReminderTable();

	

	
});
	
	

</script>

<script>

function refreshStockData(){

	checkReminderTable();
	var t = [];

	//console.log("refresh stockList:" + stockList);
	//console.log(supResPrices);
	for (var key in stockList){
		if(ticks[stockList[key]] != undefined){
			if(ticks[stockList[key]].ticks.length > 0){
				var tickLen = ticks[stockList[key]].ticks.length;
				var num = (ticks[stockList[key]].ticks[tickLen-1].value[0] - ticks[stockList[key]].price.ref) / ticks[stockList[key]].price.ref*100;
				var percent = num.toFixed(2) + "%";
				var buy5 = ticks[stockList[key]].buy5.length ? ticks[stockList[key]].buy5[0][0] : "";
				var sell5 = ticks[stockList[key]].sell5.length ? ticks[stockList[key]].sell5[0][0] : "";
				var open = ticks[stockList[key]].price.hasOwnProperty('open') ? ticks[stockList[key]].price.open : "";
				var highest = ticks[stockList[key]].price.hasOwnProperty('highest') ? ticks[stockList[key]].price.highest : "";
				var lowest = ticks[stockList[key]].price.hasOwnProperty('lowest') ? ticks[stockList[key]].price.lowest : "";
				var element = [	"",
								stockList[key],
								ticks[stockList[key]].ticks[tickLen-1].value[0],
								buy5,
								sell5,
								percent,
								ticks[stockList[key]].ticks[tickLen-1].total[1],
								ticks[stockList[key]].price.ref,
								open,
								highest,
								lowest];

				t.push(element);			
			}else{
				var element = [	"",
								stockList[key],
								"",
								"",
								"",
								"",
								"",
								ticks[stockList[key]].price.ref,
								"",
								"",
								""];

				t.push(element);			

			}
		}

	}
	stockSet = t;
	t = [];
	//console.log("stockSet");
	//console.log(stockSet);
	for(var key in supResPrices){

		if(stockList.indexOf(supResPrices[key].stockId) != -1 ){
		    var stockId = supResPrices[key].stockId;
		    if(ticks[stockId] != undefined){
			    if(ticks[stockId].ticks.length > 0){
				    var tickLen = ticks[stockId].ticks.length;
				    var close = ticks[stockId].ticks[tickLen-1].value[0];
				    var buy5 = ticks[stockId].buy5.length ? ticks[stockId].buy5[0][0] : 0;
					var sell5 = ticks[stockId].sell5.length ? ticks[stockId].sell5[0][0] : 0;
				    			
				    var element = [	"",
				    				stockId,
				    				close,
				    				buy5,
				    				sell5,
				    				supResPrices[key].sup1,
				    				supResPrices[key].sup2,
				    				supResPrices[key].sup3,
				    				supResPrices[key].res1,
				    				supResPrices[key].res2,
				    				supResPrices[key].res3];
				    t.push(element);
				}else{
					var element = [ "",
									stockId,
				    				"",
				    				"",
				    				"",
				    				supResPrices[key].sup1,
				    				supResPrices[key].sup2,
				    				supResPrices[key].sup3,
				    				supResPrices[key].res1,
				    				supResPrices[key].res2,
				    				supResPrices[key].res3];
				    t.push(element);
				}
			}
		}
	}
	reminderSet = t;
	//console.log("reminderSet:");
	//console.log(reminderSet);
	if(remindTable){
		if(!remindTable.column(0).visible()){
			remindTable.ajax.reload();
		}
		
	}else{
		console.log("remindTable doesn't exist!");
	}
	    		
	if(table){
		table.ajax.reload();
	}else{
		console.log("table doesn't exist");
	}
}

function checkReminderTable(){

	var message = "";
	
	for(var key in supResPrices){
		 var stockId = supResPrices[key].stockId;
		 if(ticks[stockId] != undefined){
			if(ticks[stockId].ticks.length > 0){
					var tickLen = ticks[stockId].ticks.length;
				    var close = ticks[stockId].ticks[tickLen-1].value[0];
				    if(close < supResPrices[key].sup1 && supResPrices[key].sup1 != 0){
				    	message = message + stockId + " 跌破 " + supResPrices[key].sup1 + "\n";
				    }if(close < supResPrices[key].sup2 && supResPrices[key].sup2 != 0){
				    	message = message + stockId +" 跌破 " + supResPrices[key].sup2 + "\n";
				    }if(close > supResPrices[key].sup3 && supResPrices[key].sup3 != 0){
				    	message = message + stockId +" 跌破 " + supResPrices[key].sup3 + "\n";
				    }if(close > supResPrices[key].res1 && supResPrices[key].res1 != 0){
				    	message = message + stockId +" 突破 " + supResPrices[key].res1 + "\n";
				    }if(close > supResPrices[key].res2 && supResPrices[key].res2 != 0){
				    	message = message + stockId +" 突破 " + supResPrices[key].res2 + "\n";
				    }if(close > supResPrices[key].res3 && supResPrices[key].res3 != 0){
				    	message = message + stockId +" 突破 " + supResPrices[key].res3 + "\n";
				    } 
			}		    
		}
	}
	if(message != ""){
		Notification.requestPermission().then(function(result) {

		if(window.Notification.permission == "granted") {
			var notification = new Notification('到價提醒通知', {
			body: message,
        });                   
        //setTimeout(function() { notification.close(); }, 5000);
        } else {
            alert('提醒失敗');
            window.Notification.requestPermission();
       }
		});
	}
	
}

	

	const { api, socket } = fugleRealtime({
        version: 'latest', 
		token: '9e325cdd419b6701be7edb38d895f940ef9b294ef90a6c2ae3366fc0f545ec27', 
		socketIo: true, 
		fetch: fetch, 
    });


    const { join, leave, ticks} = socket;
				    
    for(i = 0; i < stockList.length; i++){
    		join({symbolId: stockList[i]}, refreshStockData);
    }


</script>


    


<div class="row">
	<div class="main_content">
		<div class="screener-toolbar">
			<div class="screener-toolbar-button">
				
					<select id="categories" class="form-control" onchange="showStock()">
						<option selected value = "請選自選組合">請選自選組合</option>
						
					<?php
						mysqli_select_db($connection,DB_DATABASE);
						mysqli_query($connection, "SET NAMES utf8");
						$sql="SELECT DISTINCT groupId, groupName FROM UserGroup WHERE userName = '$userName'";
						$result = mysqli_query($connection,$sql);					
						while($row = mysqli_fetch_array($result)) {
							echo "<option value=\"" . $row['groupId'] . "\">" . $row['groupName'] . "</option>";
						}	
					?>
	                </select>
			</div>
		</div>


	
		<table id="stock-table" class="display dt-table">

		</table>

		<table id = "reminder-table" class = "display dt-table">


		</table>
	
		<div id = "stock-group-content"></div>


	

	</div>
</div>



<?php
tail();
?>