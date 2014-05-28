var start=true, startdate, enddate, globalmaxdate, occ, originalContent;

jQuery(document).ready(function($) {

	$('input[type="date"]').datepicker({
        //dateFormat : 'd-m-yy'
    }); //datepicker
    
	$('input[type="date"]').attr('readonly', 'readonly');

	$('#txl_btn_perweek').click(function(){
		$('.perweekend, .permidweek').hide();
		$('#txl_prices_result, .perweek').show()
	})

	$('#txl_btn_permidweek').click(function(){
		$('.perweekend, .perweek').hide();
		$('#txl_prices_result, .permidweek').show()
	})

	$('#txl_btn_perweekend').click(function(){
		$('.perweek, .permidweek').hide();
		$('#txl_prices_result, .perweekend').show()
	})

	realreadonly();
	
	function realreadonly(){
		$('[readonly="readonly"]').focus(function(){$(this).blur()})
	}



	$( "#txl_check" ).click(function() {
		$("#txl_dialog_msg").html('prijs en beschikbaarheid worden gecheckt');

console.log($('#txl_start').val())

		startdate=jQuery.datepicker.formatDate('@',new Date($('#txl_start').val()))/1000;
		startdate=findchangeday(startdate,-1)

		enddate=jQuery.datepicker.formatDate('@',new Date($('#txl_end').val()))/1000;
		if(enddate==startdate){enddate+=(24*60*60);}
		enddate=findchangeday(enddate,1)
		
		$.ajax({
			url: ajax_object.ajax_url,
			type: 'post',		
			data: {
				action: 'txl_ajax',
				start: startdate,
				end: enddate
			},
			dataType: "json",
			success : function(data) {
				$("#txl_dialog_msg").html(data.txl_msg);
				globalmaxdate=data.globalmaxdate;
				occ=data.occ;
				
				if(data.txl_error){
					txl_dialog_datepicker_fn();
				} 
				else{
					$('#txl_booknow').show()
					$('#txl_different_period').click(function(){
						txl_dialog_datepicker_fn();
					})
				}
			}
		})//ajax
		

	$( "#txl_dialog" ).dialog({
		maxWidth:700,
        maxHeight: 700,
        width: 700,
        height: 450,
        
        open : function(event, ui) { 
			originalContent = $("#txl_dialog").html();
		},
		close : function(event, ui) {
			$("#txl_dialog").html(originalContent);
			start=true;
		}	
	});
	
	}); //txl_check click


	
	
function txl_dialog_datepicker_fn(){

	$( "#txl_dialog_datepicker" ).datepicker({
		//numberOfMonths: 2,
		showOtherMonths: true,
		maxDate: new Date(globalmaxdate*1000),
		minDate: 0,
		beforeShowDay: function(date){
			var string = jQuery.datepicker.formatDate('@', date)/1000;
			var available =true;
			var myclass='';
			$.each(occ, function(i, v){
				if ((v[0]<string)&&(v[1]>string)) {available=false}
			});
			if (((startdate<=string)&&(string<=enddate))||startdate==string){myclass='myselected'}

			return [available, myclass]
		},
		onSelect: function(mydate, field){
			if (start){
				var mymaxdate=globalmaxdate;
				
				startdate=jQuery.datepicker.formatDate('@',new Date(mydate))/1000;
				startdate=findchangeday(startdate,-1)
				$('#txl_dialog_msg').html('start: '+jQuery.datepicker.formatDate('DD, d MM yy', new Date (startdate*1000)));
				$('#txl_booknow').hide()
				enddate=null;
						   
				$.each(occ, function(i, v){
					if ((v[0]<mymaxdate)&&(v[0]>startdate)) {mymaxdate=v[0]}
				});
				
				$(this).datepicker("option", "minDate", new Date(startdate*1000));
				$(this).datepicker("option", "maxDate", new Date(mymaxdate*1000));
				
				start=false;
			}
			else
			{
				
				enddate=jQuery.datepicker.formatDate('@', $(this).datepicker( 'getDate' ))/1000;
				if(enddate==startdate){enddate+=(24*60*60);}
				enddate=findchangeday(enddate,1)
				$('#txl_dialog_msg').html('start: '+jQuery.datepicker.formatDate('DD, d MM yy', new Date (startdate*1000))+
					' end: '+jQuery.datepicker.formatDate('DD, d MM yy', new Date (enddate*1000)));
				$('#txl_booknow').show()
				
				$(this).datepicker("option", "minDate",0);
				$(this).datepicker("option", "maxDate",new Date(globalmaxdate*1000));
				
				start=true;        
			}
			
		}
	});
} //step2datepicker
	
	$('#txl_booknow').click(function(){
		$('#txl_dialog').load(ajax_object.ajax_url,{
				action: 'txl_booking_form',
				start: startdate,
				end: enddate
		},function(){
			realreadonly();
		
			$('.extracheckbox').each(function(){
				calc_extras($(this));
			})			
			$('.extracheckbox').click(function(){
				calc_extras($(this));
			}),
			$('select[name="persons"]').change(function(){
				$('.extracheckbox').each(function(){
					calc_extras($(this));
				})	
			}),
			$('#txl_booknow_final').click(function(){
				var valid=true;
				$('[required]').each(function(i){
					$(this).next('.error').remove()
					if($(this).val()==''){
						$(this).after( '<span class="error">this field is required</span>' );
						valid=false;
					}
					else if (($(this).attr('type')=='email')&&!isValidEmailAddress($(this).val())){
							$(this).after( '<span class="error">please provide a valid email address</span>' );
							valid=false;
					}
				})
				if (valid){
					var datatosend=$("#bookingform").serializeArray()
					datatosend.push({name: 'action', value: 'txl_booking_final_send'});
					$('#txl_dialog').load(ajax_object.ajax_url,datatosend);
				}
			}) //booknowfinal click

			
			
		})
	}) //booknow click
	
	
function calc_extras(thisone){
	var subtotal=0, total=0;	
	subtotal=thisone.data('price');
	
	if (thisone.data('perperson')=='per person'){subtotal*=$("select[name='persons']").val()}
	if (thisone.data('perstay')=='per day'){subtotal*=$("input[name='days']").val()}
	subtotal=Math.round(subtotal*100)/100;
	console.log(thisone.prop('checked') )
	if (thisone.is(':checked')){
		thisone.parents('tr').find('.subtotal').val(subtotal.toFixed(2));
	}
	else{
		thisone.parents('tr').find('.subtotal').val('');
	}
	$('.subtotal').each(function(){
		if(!isNaN($(this).val())){
			total+=$(this).val()*1;
		}
	})	
	$('#total').val(total.toFixed(2))
	
}//calc_extras



}) //jquery


function findchangeday(timestamp,direction){
	var clone=new Date(timestamp*1000)
   // console.log(clone)
	var weekday=clone.getDay()
	if (direction==-1){var week=new Array (-2,0,-1,-2,-3,0,-1)}
	else {var week=new Array (1,0,3,2,1,0,2)}
	clone.setDate(clone.getDate()+week[weekday])
	return jQuery.datepicker.formatDate('@', clone)/1000;
	}

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};