<?php
# Check login, if not, exit
require_once('authenticate.php');

# Print HTML content
require_once('html.php');
head(true);
?>

<!-- Load Datepicker -->
<script src="../vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script src="../vendor/bootstrap-datepicker/js/locales/bootstrap-datepicker.zh-TW.js"></script>
<link rel="stylesheet" href="../vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" />

<!-- Timepicker JS file -->
<script src="../dist/js/jquery.timepicker.js"></script>
<!-- Timepicker CSS file -->
<link rel="stylesheet" href="../dist/css/jquery.timepicker.min.css">


<!-- recordAdd.html -->
<link href="../dist/css/dynamic-form.css" rel="stylesheet">

<div class="row">
	<div class="col-lg-12">

	    <!-- HIDDEN DYNAMIC ELEMENT TO BE CLONED -->
		<div class="form-group dynamic-element form-element form-trans" style="display:none">
			<!-- Delete btn-->
			<button type="button" class="btn btn-danger delete" style="">X</button>
			<!-- Date -->
			<div class="row">
				<div class="col-xs-4 col-sm-4 col-md-4 col-lg-3">
				    <label for="date">日期</label>
			        <input class="form-control" id="date" name="date" placeholder="請點選日期" type="text" autocomplete="off" required/>
	        	</div>
	        	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-3">
	        		<label for="date">時間</label>
			        <input class="form-control timepicker" id="time"  name="time" placeholder="HH:MM" type="text" required/>
	        	</div>
	        	<div class="col-xs-0 col-sm-0 col-md-0 col-lg-4">
	        	</div>
	        </div>
	        <!-- Type -->
	        <div class="row row-margin-top">
	        	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-3">
			        <label>交易類型</label>
		            <div class="form-radio">
		            	<input class="radio-btn" type="radio" name="notSetRadios" value="buy" checked="checked">買進
		            	<input class="radio-btn" type="radio" name="notSetRadios" value="sell">賣出
		        	</div>
		        </div>
            </div>

            <!-- Amount -->
            <div class="row row-margin-top">
				<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
	        		<label>張數</label>
			        <input class="form-control" name="amount" type="number" min="1" step="1" placeholder="請輸入成交張數" required/>
	        	</div>
            </div>

            <!-- Price -->
	        <div class="row row-margin-top">
	        	<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
			        <label>成交價格</label>
		            <input class="form-control" name="price" type="number" min="0" step="0.01" placeholder="請輸入成交價格" required/>
		        </div>
            </div>
            <!-- Transaction ID -->
            <input type="hidden" name="id">
		</div>
		<!-- END OF HIDDEN ELEMENT -->

	    <div class="form-container">
		    <form class="form-horizontal" action="recordSubmit.php" method="post">
		        <fieldset>
		        	<!-- Form Name -->
		            <legend class="title">輸入交易明細</legend>

		            <!-- Select broker -->
		            <div class="form-element">
		            	<div class="row">
		            		<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
					            <label>股票代碼</label>
					            <input class="form-control" id="stock-id" name="stock" placeholder="請輸入股票代碼(四碼)" type="number" min="1000" max="9999" step="1" required/>
				        	</div>
				        	<!-- /.col-lg-6 -->
			            </div>
			            <!-- /.row -->
			        </div>
					<!-- /.form-element -->

		            <!-- Dynamic element will be cloned here, adding 1 per click -->
		            <div class="dynamic-stuff">
		            
		            <!-- You can call clone function once if you want it to show it a first element-->
		            </div>

		            <!-- Button -->
		            <div class="form-group">
		                <div class="row">
		                	<div class="center">
		                        <button type="button" class="add-one btn btn-success">新增交易</button>
		                    </div>
		                </div>
		                <!-- /.row -->
		                <div class="row">
		                    <!-- /.col-md-12 -->
		                    <div class="center">
		                        <button id="singlebutton" name="singlebutton" class="btn btn-primary">確認提交</button>
		                    </div>
		                    <!-- /.col-md-6 -->
		                </div>
		                <!-- /.row -->
		            </div>
		            <!-- /.form-group -->
		        </fieldset>
		    </form>
		</div>
		<!-- /.form-container -->

	</div>
	<!-- /.col-lg-12 -->
</div> 
<!-- /.row -->

<!-- JS for clicking button -->
<script type="text/javascript">
var clickNum = 0;
$('.add-one').click(function(){
  // Clone the hidden element and shows it
  $('.dynamic-element').first().clone().appendTo('.dynamic-stuff').fadeIn(400);

  // Init date picker
  $('input[name="date"]').datepicker({
		    format: "yyyy-mm-dd",
		    autoclose: true,
		    orientation: "bottom auto",
		    //startDate: "today",
		    clearBtn: true,
		    //calendarWeeks: true,
		    todayHighlight: true,
		    language: 'zh-TW'
  });



  $('.timepicker').timepicker({
  	    timeFormat: 'HH:mm',
	    interval: 1,
	    minTime: '9:00',
	    maxTime: '13:30',
	    defaultTime: '9',
	    startTime: '10:00',
	    dropdown: true,
	    scrollbar: true

  });

	// Set name for each field, so that server can parse data one by one
	clickNum = clickNum + 1;
	//console.log('buyOrSell'+clickNum);
	$('.dynamic-element').last().find("input[name='date']").attr('name','date'+clickNum);
	$('.dynamic-element').last().find("input[name='time']").attr('name','time'+clickNum);
	$('.dynamic-element').last().find("input[name='notSetRadios']").attr('name','buyOrSell'+clickNum);
	$('.dynamic-element').last().find("input[name='amount']").attr('name','amount'+clickNum);
	$('.dynamic-element').last().find("input[name='price']").attr('name','price'+clickNum);
	$('.dynamic-element').last().find("input[name='id']").attr('name',clickNum);
  	attach_delete();
});

$('.add-one').click();

//Attach functionality to delete buttons
function attach_delete(){
  $('.delete').off();
  $('.delete').click(function(){
    //console.log("click");
    $(this).closest('.form-group').fadeOut(400, function(){
    	$(this).closest('.form-group').remove();
    });
  });
}
</script>


<?php
tail();
?>
