<?php
if ($_REQUEST['submitted']){
	update_option('txl_extra',flip($_REQUEST['txl_extras']));
}

$txl_extras=get_option('txl_extra');

if (!is_array($txl_extras)){
	$txl_extras=array(
		array(
			'description'=>'',
			'price'=>0,
			'required'=>'',
			'perperson'=>'',
			'perstay'=>''
			)
		);
}



?>

<style>
input[type="number"] {text-align: right !important;}

input[type="text"], .deleterow {background-color: white; 
border-color: #DDDDDD;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.07) inset;
       border-style: solid;
    border-width: 1px;
</style>
<script>

jQuery(document).ready(function($) {

	$('#newrow').click(function () {
		$('#txl_table > tbody:last').append('<tr><td><input name="txl_extras[description][]" type="text"></td><td><input name="txl_extras[price][]]" type="number" step="0.01"></td><td><select name="txl_extras[required][]"><option selected>required</option><option>not required</option></select></td><td><select name="txl_extras[perperson][]"><option selected>per person</option><option>per group</option></select></td><td><select name="txl_extras[perstay][]"><option selected>per day</option><option>per stay</option></select><td><input type="button" value="-" class="deleterow"/></td></tr>');

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

<form method="post" id="txl_form">
<table id="txl_table">
<tr><th>description</th><th>price</th><th>required?</th><th>per person?</th><th>per day?</th></tr>
<?php
foreach ($txl_extras as $row){
?>
	<tr>
		<td><input name="txl_extras[description][]" value="<?=$row['description']?>" type="text"></td>
		<td><input name="txl_extras[price][]" value="<?=makedecimals($row['price'],2)?>" type="number" step="0.01"></td>
		<td><select name="txl_extras[required][]">
			  <option selected><?=$row['required']?></option>
			  <option>required</option>
			  <option>not required</option>
			</select> 
		</td>
		<td><select name="txl_extras[perperson][]">
			  <option selected><?=$row['perperson']?></option>
			  <option>per person</option>
			  <option>per group</option>
			</select> 
		</td>
		<td><select name="txl_extras[perstay][]">
			  <option selected><?=$row['perstay']?></option>
			  <option>per day</option>
			  <option>per stay</option>
			</select> 
		</td>
		<td><input type="button" value="-" class="deleterow button action"/></td>
	</tr>
<?php
}
?>

</table>

<input type="button" value="new row" id="newrow" class="button action"/><br/>

    
<input type="submit" name="submitted" value="ok" class="button action">    
</form>