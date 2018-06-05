<?php
require_once('authenticate.php');
?>



<!-- recordAdd.html -->
<link href="../dist/css/dynamic-form.css" rel="stylesheet">

<div class="row">
	<div class="col-lg-12">

	    <!-- HIDDEN DYNAMIC ELEMENT TO BE CLONED -->
		<div class="form-group dynamic-element form-element" style="display:none">
			<!-- Date -->
			<div class="row">
				<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
				    <label for="date">日期</label>
			        <input class="form-control" id="date" name="date" placeholder="YYYY-MM-DD" type="text"/>
	        	</div>
	        	<div class="col-xs-0 col-sm-1 col-md-5 col-lg-6"></div>
	        	<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
	        		<button type="button" class="btn btn-danger delete" style="margin-top:27px;">刪除此筆交易</button>
	        	</div>
	        </div>
	        <!-- Transaction -->
	        <div class="row">
	        	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			        <label>成交明細</label>
		            <textarea class="form-control" name="transaction" rows="5"></textarea>
            	</div>
            </div>
		</div>
		<!-- END OF HIDDEN ELEMENT -->

	    <div class="form-container">
		    <form class="form-horizontal" method="post">
		        <fieldset>
		        	<!-- Form Name -->
		            <legend class="title">輸入交易明細</legend>

		            <!-- Select broker -->
		            <div class="form-element">
		            	<div class="row">
		            		<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
					            <label>券商格式選擇</label>
					            <select class="form-control">
					                <option>凱基</option>
					                <option>日盛</option>
					                <option>群益</option>
					                <option>麥當勞</option>
					                <option>肯德基</option>
					            </select>
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

<!-- Load Datepicker -->
<script src="../vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script src="../vendor/bootstrap-datepicker/js/locales/bootstrap-datepicker.zh-TW.js"></script>
<link rel="stylesheet" href="../vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" />

<!-- JS for clicking button -->
<script type="text/javascript">
var clickNum = 0;
$('.add-one').click(function(){
  // Clone the hidden element and shows it
  $('.dynamic-element').first().clone().appendTo('.dynamic-stuff').show();

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

	// Set name for each field, so that server can parse data one by one
	clickNum = clickNum + 1;
	//console.log('buyOrSell'+clickNum);
	$('.dynamic-element').last().find("input[name='date']").attr('name','date'+clickNum);
	$('.dynamic-element').last().find("textarea[name='transaction']").attr('name','transaction'+clickNum);
  	attach_delete();
});

$('.add-one').click();

//Attach functionality to delete buttons
function attach_delete(){
  $('.delete').off();
  $('.delete').click(function(){
    console.log("click");
    $(this).closest('.form-group').remove();
  });
}
</script>