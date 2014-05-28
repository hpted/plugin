jQuery(document).ready(function($) {
	$('input[type="date"]').datepicker({
        dateFormat : 'd-m-yy'
    });
    
	$('input[type="date"]').attr('readonly', 'readonly');

	$('input[type="number"]').change(function (event){
		if(isNaN($(this).val())){$(this).val('')}
	});

}); //jquery