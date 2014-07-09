<?php
if ($_REQUEST['submitted']){
	$txl_date=$_REQUEST['txl_date'];
	$txl_week=$_REQUEST['txl_week'];
	$txl_weekend=$_REQUEST['txl_weekend'];
	$txl_midweek=$_REQUEST['txl_midweek'];

	$txl_real_date=array_map("strtotime", $txl_date);

	array_multisort($txl_real_date, $txl_date, $txl_week, $txl_weekend, $txl_midweek);	

	update_option('txl_real_date',$txl_real_date);
	update_option('txl_date',$txl_date);
	update_option('txl_week',$txl_week);
	update_option('txl_weekend',$txl_weekend);
	update_option('txl_midweek',$txl_midweek);

	update_option('txl_enddate',$_REQUEST['txl_enddate']);
}

$txl_date=get_option('txl_date');
$txl_week=get_option('txl_week');
$txl_weekend=get_option('txl_weekend');
$txl_midweek=get_option('txl_midweek');

wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');



?>
<script>

jQuery(document).ready(function($) {
	$('input[type="date"]').datepicker({
        dateFormat : 'd-m-yy'
    });
    
	$('input[type="date"]').attr('readonly', 'readonly');


	$('#newrow').click(function () {
		$('#txl_table > tbody:last').append('<tr><td><input name="txl_date[]" type="date" readonly value="'+$('td input[type="date"]:last').val()+'"></td><td><input name="txl_week[]" type="number"></td><td><input name="txl_weekend[]" type="number"></td><td><input name="txl_midweek[]" type="number"></td><td><input type="button" value="-" class="deleterow"/></td></tr>');
		$('input[type="date"]').datepicker({
        	dateFormat : 'd-m-yy'
    	});
	})

$('body').on('click', '.deleterow', function (event){
	if($('.deleterow').length>1){
		$(this).closest('tr').remove();
	}
});

$('body').on('change', '[type="number"]', function (event){
	if(isNaN($(this).val())){$(this).val('')}
});








}); //jquery


</script>
<style>
input[readonly], .deleterow {background-color: white; 
border-color: #DDDDDD;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.07) inset;
       border-style: solid;
    border-width: 1px;

}
</style>

<form method="post" id="txl_form">
<table id="txl_table">
<tr><th>datum</th><th>week</th><th>weekend</th><th>midweek</th></tr>
<?php
foreach ($txl_date as $key => $value){
?>
	<tr>
		<td><input name="txl_date[]" value="<?=$value?>" type="date"></td>
		<td><input name="txl_week[]" value="<?=$txl_week[$key]?>" type="number"></td>
		<td><input name="txl_weekend[]" value="<?=$txl_weekend[$key]?>" type="number"></td>
		<td><input name="txl_midweek[]" value="<?=$txl_midweek[$key]?>" type="number"></td>
		<td><input type="button" value="-" class="deleterow button action"/></td>
	</tr>
<?php
}
?>

</table>

<input type="button" value="new row" id="newrow" class="button action"/><br/>

bookable till: <input name="txl_enddate" value="<?=get_option('txl_enddate')?>" type="date"><br/>
    
    <input type="submit" name="submitted" value="ok"  class="button action">    
</form>


<!--
to do:






-->