<?php
if ($_REQUEST['submitted']){


if (is_numeric($_POST['txl_max_people'])){
	update_option('txl_max_people',$_POST['txl_max_people']);
}

if (is_email($_POST['txl_from'])){
	update_option('txl_from',$_POST['txl_from']);
}

update_option('txl_cc',is_emails($_POST['txl_cc']));
update_option('txl_bcc',is_emails($_POST['txl_bcc']));
update_option('txl_mail_text',$_POST['txl_mail_text']);
update_option('txl_success_text',$_POST['txl_success_text']);


}



function is_emails($in){


	$return_string='';
	$validate_array=explode(',', $in);
	foreach ($validate_array as $item){
		$item=trim($item);
		if (is_email($item)) {
			$return_string.=$item.', ';
		}
	}
	return substr($return_string, 0, -2);
}




?>

<style>
input[type="number"] {text-align: right !important;}

input[type="text"], .deleterow {
	background-color: white; 
	border-color: #DDDDDD;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.07) inset;
	border-style: solid;
	border-width: 1px;
}

.inputxl {width: 40em;}


</style>
<script>

jQuery(document).ready(function($) {



}); //jquery


</script>

<h3>Settings</h3>

<form method="post" id="txl_form">

<label for="txl_max_people">Max number of people: </label><br>
<input name="txl_max_people" value="<?=get_option('txl_max_people')?>" type="number"><br>
<br>

<label for="txl_from">From: </label>(fill in a single email address)<br>
<input name="txl_from" value="<?=get_option('txl_from')?>" type="email" class="inputxl"><br>
<br>

<label for="txl_cc">Cc: </label> (one or more, separated by ,)<br>
<input name="txl_cc" value="<?=get_option('txl_cc')?>" type="email" multiple="multiple" class="inputxl"><br>
<br>
<label for="txl_bcc">Bcc: </label> (one or more, separated by ,)<br>
<input name="txl_bcc" value="<?=get_option('txl_bcc')?>" type="email" multiple="multiple" class="inputxl"><br>

<div style="max-width: 80em;">
<br><br>
Mail text:<br>
<?= wp_editor( get_option('txl_mail_text'), 'txl_mail_text', array('textarea_name' => 'txl_mail_text', 'teeny' => true, 'media_buttons' => false)  )?>
<br><br>

Success text:<br>
<div style="max-width: 80em;">
<?= wp_editor( get_option('txl_success_text'), 'txl_success_text', array('textarea_name' => 'txl_success_text', 'teeny' => true, 'media_buttons' => false)  )?>
</div>
<br>
<input type="submit" name="submitted" value="submit" class="button action">    
</form>


<!--
to do:
inleiding





-->