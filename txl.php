<?php
/**
 * @package txl booking
 * @version 0.1
 */
/*
Plugin Name: txl booking
Plugin URI: http://texellastminutes.nl/plugin
Description: a plugin to handle bookings
Author: Hans-Peter van Leeuwen
Version: 0.1
Author URI: http://ha.nspeter.nl
*/

function txl_activation() {
	$txl_empty_array=array(0);
	add_option( 'txl_real_date', $txl_empty_array, '', 'yes' );
	add_option( 'txl_date', $txl_empty_array, '', 'yes' );
	add_option( 'txl_week', $txl_empty_array, '', 'yes' );
	add_option( 'txl_weekend', $txl_empty_array, '', 'yes' );
	add_option( 'txl_midweek', $txl_empty_array, '', 'yes' );
	add_option( 'txl_extras', $txl_empty_array, '', 'yes' );
	

}
register_activation_hook(__FILE__, 'txl_activation');

function txl_deactivation() {
	delete_option('txl_date');
	delete_option('txl_date');
	delete_option('txl_week');
	delete_option('txl_weekend');
	delete_option('txl_midweek');
}
register_deactivation_hook(__FILE__, 'txl_deactivation');

add_action( 'admin_menu', 'register_my_custom_menu_page' );

function register_my_custom_menu_page(){
    add_menu_page( 'Settings for bookings plugin', 'Bookings', 'manage_options', 'txl/txl-admin.php', '', plugins_url( 'txl/images/icon.png' ), 74 );

	add_submenu_page( 'txl/txl-admin.php', 'Settings for bookings plugin', 'Bookings settings', 'manage_options', 'txl/txl-admin.php', '');
	add_submenu_page( 'txl/txl-admin.php', 'Prices', 'Prices', 'manage_options', 'txl/txl-admin-prices.php', '');
	add_submenu_page( 'txl/txl-admin.php', 'Extras', 'Extras', 'manage_options', 'txl/txl-admin-extras.php', '');
    
}



function txl_enqueue_style() {
	wp_enqueue_style('txl_css', plugins_url('/txl/style.css'), false ); 
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

}

function txl_enqueue_script() {
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('txl_js', plugins_url('/txl/front-end.js'), false );

	wp_localize_script( 'txl_js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
}

add_action( 'wp_enqueue_scripts', 'txl_enqueue_style' );
add_action( 'wp_enqueue_scripts', 'txl_enqueue_script' );



function txl_insert_here(){

	$txl_date=get_option('txl_date');
	$txl_week=get_option('txl_week');
	$txl_weekend=get_option('txl_weekend');
	$txl_midweek=get_option('txl_midweek');
	$txl_date_count=count($txl_date)-1;

	$txl_prices_table='<table id="txl_prices_result"><tr><th>van</th><th>tot</th><th class="perweek">week</th><th class="perweekend">weekend</th><th class="permidweek">midweek</th></tr>';
	foreach ($txl_date as $key => $value){
		if ($key<$txl_date_count){
			$txl_till=$txl_date[$key+1];
			}
		else {
			$txl_till = get_option('txl_enddate');
			}
		
		$txl_prices_table.='<td>'.$value.'</td><td>'.$txl_till.'</td><td class="perweek">'.$txl_week[$key].'</td><td class="perweekend">'.$txl_weekend[$key].'</td><td class="permidweek">'.$txl_midweek[$key].'</td></tr>';
	}

	$txl_prices_table.='</table>';

	return '<label for="txl_start">van</label><input type="date" name="txl_start" id="txl_start">
			<label>tot</label><input type="date" name="txl_end" id="txl_end"><br>
			<button id="txl_check">check live beschikbaarheid en prijs</button>
			<div id="txl_dialog" class="wp-dialog">
				<div id="txl_dialog_msg"></div>
				<div id="txl_dialog_datepicker"></div>
				<div id="txl_showprices">toon prijzen per 
					<span id="txl_btn_perweek" class="txl_btn">week</span>/
					<span id="txl_btn_perweekend" class="txl_btn">weekend</span>/
					<span id="txl_btn_permidweek" class="txl_btn">midweek</span>
				'.$txl_prices_table.'
				</div>
				
				
				<button id="txl_booknow">book now!</button>
			</div>' ;
}

add_shortcode( 'bookable', 'txl_insert_here' );

function txl_ajax(){
	
	//print_r($_POST);
	//die();
	
	$txl_error=false;
	$txl_msg='';
	
	$start=$_POST['start'];
	$end=$_POST['end'];
	
	if (!($start&&$end)){
		$txl_msg='Choose a start date and an end date';
		$txl_error=true;
	}
	
	if (!$txl_error){
		$txl_meta = array(
			array(
				'key' => 'start_date',
				'value' => $start,
				'compare' => '<',
				'type' => 'NUMERIC'
			),
			array(
				'key' => 'end_date',
				'value' => $start,
				'compare' => '>',
				'type' => 'NUMERIC'
			)
		);

		if (count_posts($txl_meta)>0){
			$txl_error=true;
			$txl_msg='Selected period is not available.';
			}
		
		if (!$txl_error){
			$txl_meta = array(
				array(
					'key' => 'start_date',
					'value' => $end,
					'compare' => '<',
					'type' => 'NUMERIC'
				),
				array(
					'key' => 'end_date',
					'value' => $end,
					'compare' => '>',
					'type' => 'NUMERIC'
				)
			);

			if (count_posts($txl_meta)>0){
				$txl_error=true;
				$txl_msg='Selected period is not available.';
			}
			if (!$txl_error){
				$txl_meta = array(
					array(
						'key' => 'start_date',
						'value' => $start,
						'compare' => '>',
						'type' => 'NUMERIC'
					),
					array(
						'key' => 'start_date',
						'value' => $end,
						'compare' => '<',
						'type' => 'NUMERIC'
					)
				);
				if (count_posts($txl_meta)>0){
					$txl_error=true;
					$txl_msg='Selected period is not available.';
				}				
			}	
		}
	}	
	
	if (!$txl_error){
		$costa=costa($start,$end);
		$txl_msg.='Success! This period is available. Rent is '.$costa.'<p>(you can also <span id="txl_different_period">select a different period</span>)</p>';
	}
	else{
		$txl_msg=$txl_msg.'<br>Select a different period.<br>(Holidays start and end on Monday or Friday)';
	}
	
	$response=array(
		'price'=>costa($start,$end),
		'start'=>$start,
		'end'=>$end,
		'txl_msg'=> $txl_msg,
		'txl_error'=> $txl_error,
		'globalmaxdate'=>strtotime(get_option('txl_enddate')),
		'occ'=>txl_get_occ()
	);
	
	
	echo json_encode($response);
		
	die(); 

}//txl_ajax

function txl_booking_form (){
	$start=$_POST['start'];
	$end=$_POST['end'];
	$days=($end-$start)/(60*60*24);
	
	$txl_extras=get_option('txl_extra');
	
	echo '
	<form id="bookingform">
	<!--start: '.$start.', end: '.$end.'-->
	<h4>Our holiday from '.date('l, j F Y ', $start).' till '. date('l, j F Y ', $end).' ('.$days.' days)</h4>
	<p>rent is '.costa($start, $end).'</p>

	<input type="hidden" name="start" value="'.date('l, j F Y ', $start).'"> 
	<input type="hidden" name="end" value="'. date('l, j F Y ', $end).'">

	<input type="hidden" name="start_date" value="'.date('j F Y ', $start).'"> 
	<input type="hidden" name="end_date" value="'. date('j F Y ', $end).'">
	<input type="hidden" name="booking_date" value="'. date('j F Y ', 0).'">
	<input type="hidden" name="days" value="'.$days.'">
	
	<h4>my contact details</h4>
	<table>
	<tr><td></td><input type="radio" name="sex" value="Ms." checked="checked"> Ms. / <input type="radio" name="sex" value="Mr"> Mr.</td></tr>
	<tr><td>full name: </td><td><input name="name" autofocus required><span></span></td></tr>
	<tr><td>addres: </td><td><input name="addres" required><span></span></td></tr>
	<tr><td>postcode: </td><td><input name="postcode" required><span></span></td></tr>
	<tr><td>city: </td><td><input name="city" required><span></span></td></tr>
	<tr><td>email: </td><td><input name="email" type="email" required><span></span></td></tr>
	<tr><td>phone: </td><td><input name="phone" required><span></span></td></tr>
	</table>

	<p>my group is <select name="persons">
	<option selected>';
	
	for ($i=get_option('txl_max_people'); $i>1; $i--){
		echo $i.'</option>
		<option>';
	}
	echo'1</option>
	</select> persons</p>
	
	<h4>extra\'s</h4>
	';
	
	foreach ($txl_extras as $key=>$row){
		if (strrpos($row['required'],'not')===0) {$readonly='';} else {$readonly='  style="visibility:hidden" checked ';}  
		echo '<p><input type="checkbox" name="extras[]" '.$readonly.' class="extracheckbox" value="'.$row['description'].'" data-price="'.$row['price'].'" data-perperson="'.$row['perperson'].'" data-perstay="'.$row['perstay'].'"> '.$row['description'] .' '.number_format($row['price'],2).' ('.$row['perstay'].', '.$row['perperson'].') <input class="subtotal" name="'.$row['description'].'" readonly></p>';
	}
	
	echo 'total extras: <input id="total" name="totalextras" readonly>
	<br>
	additional comments:<br>
	<textarea name="comments"></textarea>
	
	</form>
	<button id="txl_booknow_final" style="display: inline-block;">book now!</button>
	';
	die();
	
} //txl_booking_form

function txl_get_occ(){
	
	$txl_meta= array(
		'key' => 'start_date',
		'value' => strtotime("now"),
		'compare' => '>',
		'type' => 'NUMERIC'
	);
	$args = array(
		'post_type' => 'booking',
		'posts_per_page' => -1,

		'meta_query' => $txl_meta
	);
	// Make the query
	$bookings_query = new WP_query();
	$bookings_query->query($args);

	while ( $bookings_query->have_posts() ) {
		$bookings_query->the_post();
		
		$occ[]=array(
			(int)get_post_meta($bookings_query->post->ID, 'start_date', true),
			(int)get_post_meta($bookings_query->post->ID, 'end_date', true)
		);
	}	
	
	return $occ;

} //txl_get_occ

function count_posts($txl_meta){
	$args = array(
		'post_type' => 'booking',
		'posts_per_page' => -1,

		'meta_query' => $txl_meta
	);
	// Make the query
	$bookings_query = new WP_query();
	$bookings_query->query($args);

	return $bookings_query->post_count;

}


function costa($start, $end){
	$costa=0;
	$day=60*60*24;

	for ($i=$start; $i<$end; $i+=7*$day){
		$costanow=costanow($i);
	  	$costa+=$costanow['week'];
	} 

	if (round($i-$end)==4){
		$costanow=costanow($i-7*day);
	  	$costa+=$costanow['weekend'];
	}
	else if (round($i-$end)==3){
		$costanow=costanow($i-7*day);
	  	$costa+=$costanow['midweek'];
	}

	return number_format($costa,2);

}//costa

function costanow($costa_date){
	$costa_dates=get_option('txl_real_date');
	
	$txl_week=get_option('txl_week');
	$txl_weekend=get_option('txl_weekend');
	$txl_midweek=get_option('txl_midweek');
	
	foreach($costa_dates as $key=>$value) {
		if ($value<=$costa_date){$counter=$key;}
	}
	
	return array('week'=>$txl_week[$counter], 'weekend'=>$txl_weekend[$counter], 'midweek'=>$txl_midweek[$counter] );
}//costanow


function txl_booking_final_send(){


	$title=$_POST['sex'].' '.$_POST['name'].' ('.$_POST['start'].' - '.$_POST['end'].') '.$_POST['phone'];
	$title=sanitize_text_field($title);

	
	$txl_content='<table>
		<tr><td>full name: </td><td>'.$_POST['sex'].' '.$_POST['name'].'</td></tr>
		<tr><td>address: </td><td>'.$_POST['addres'].'</td></tr>
		<tr><td>postcode: </td><td>'.$_POST['postcode'].'</td></tr>
		<tr><td>city: </td><td>'.$_POST['city'].'</td></tr>
		<tr><td>phone: </td><td>'.$_POST['phone'].'</td></tr>
		<tr><td>group of: </td><td>'.$_POST['persons'].' persons</td></tr>
		<tr><td colspan=2><b>extras</b></td></tr>';	
	
	$txl_message='';
			
	$extras=$_POST['extras'];
	foreach ($extras as $extra){
		$txl_content.='<tr><td>'.$extra.': </td><td>'.$_POST[str_replace(' ','_',$extra)].'</td></tr>';
		$txl_message.=$extra.': '.$_POST[str_replace(' ','_',$extra)]. "\r\n";
	}
	
	$txl_content.='<tr><td>total extras: </td><td>'. $_POST['totalextras'].'</td></table>';
	$txl_content.='additional comments: <br>'.$_POST['comments'];
	
	$txl_message.='total extras: '. $_POST['totalextras']. "\r\n";

	
	// Create post object
	$my_post = array(
	  'post_title'    => $title,
	  'post_content'    => $txl_content,
	  'post_type'  => 'booking',
	);

	// Insert the post into the database
	$post_id = wp_insert_post( $my_post, false );
	//now you can use $post_id withing add_post_meta or update_post_meta
	
	if($post_id==0){
		echo 'error!';
		die();
	}
	
	$txl_fields=array('name', 'email', 'start_date', 'end_date','booking_date'); 
	foreach ( $txl_fields as $field ) {
		txl_save($post_id, $field);
	}
	
	$start=strtotime($_POST['start_date']);
	$end=strtotime($_POST['end_date']);
	$rent=costa($start,$end);
	
	update_post_meta( $post_id, 'total', $rent );
	
	$txl_message=$txl_error."\r\n".
		'Dear '.$_POST['sex'].' '.$_POST['name']."\r\n"."\r\n".
		get_option('txl_mail_text')."\r\n"."\r\n".
		'Your holiday is from '.$_POST['start'].' till '.$_POST['end']. "\r\n" .
		'Your rent is '.$rent.'.'. "\r\n" . "\r\n".
		'The following costs are paid to the concierge:'."\r\n".
		$txl_message."\r\n"."\r\n".
		'Your had these additional comments:'."\r\n".$_POST['comments'];

	$txl_to=$_POST['email'];
	if (!is_email($txl_to)){
		$txl_error.='<p class="error">There was an error sending the confirmation email. Please contact the administrator.</p>';
		$txl_to=get_settings('admin_email');
	}
	
	$headers='From: '.get_option('txl_from') . "\r\n" .
		'Cc: '.get_option('txl_cc'). "\r\n".
		'Bcc: '.get_option('txl_bcc');
		
	wp_mail( $txl_to, 'Your holiday at '.get_settings('blogname'), $txl_message, $headers); 	

	echo str_replace("[booking_id]",$post_id,get_option('txl_success_text')).$txl_error;

	
	
	die();
} //txl_booking_final_send



add_action( 'wp_ajax_txl_ajax', 'txl_ajax' );
add_action( 'wp_ajax_nopriv_txl_ajax', 'txl_ajax' );

add_action( 'wp_ajax_txl_booking_form', 'txl_booking_form' );
add_action( 'wp_ajax_nopriv_txl_booking_form', 'txl_booking_form' );

add_action( 'wp_ajax_txl_booking_final_send', 'txl_booking_final_send' );
add_action( 'wp_ajax_nopriv_txl_booking_final_send', 'txl_booking_final_send' );

add_action( 'init', 'register_cpt_booking' );
    
function register_cpt_booking() {
    $labels = array(
    'name' => _x( 'Bookings', 'booking' ),
    'singular_name' => _x( 'Booking', 'booking' ),
    'add_new' => _x( 'Add New', 'booking' ),
    'add_new_item' => _x( 'Add New Booking', 'booking' ),
    'edit_item' => _x( 'Edit Booking', 'booking' ),
    'new_item' => _x( 'New Booking', 'booking' ),
    'view_item' => _x( 'View Booking', 'booking' ),
    'search_items' => _x( 'Search Bookings', 'booking' ),
    'not_found' => _x( 'No bookings found', 'booking' ),
    'not_found_in_trash' => _x( 'No bookings found in Trash', 'booking' ),
    'parent_item_colon' => _x( 'Parent Booking:', 'booking' ),
    'menu_name' => _x( 'Bookings', 'booking' ),
    );
    $args = array(
    'labels' => $labels,
    'hierarchical' => false,
    'supports' => array( 'title', 'editor' ),
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_nav_menus' => false,
    'publicly_queryable' => true,
    'exclude_from_search' => true,
    'has_archive' => false,
    'query_var' => true,
    'can_export' => true,
    'rewrite' => false,
    'capability_type' => 'post'
    );
    register_post_type( 'booking', $args );
} //register_cpt_booking

add_action( 'add_meta_boxes', 'booking_meta_box' );
function booking_meta_box() {
    add_meta_box( 
        'booking_meta_box',
        'Booking',
        'booking_meta_box_content',
        'booking',
        'normal',
        'high'
    );
}

function booking_meta_box_content($post){

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script('txl_inputvalidate', plugins_url('/txl/inputvalidate.js'), false ); 
	wp_enqueue_style( 'jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', true);
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'booking_meta_box_content_nonce' );
	echo '<table>';
	echo txl_meta_input($post->ID, 'name', 'name', 'text');
	echo txl_meta_input($post->ID, 'email', 'email', 'text');
	echo txl_meta_input($post->ID, 'start_date', 'start date', 'date');
	echo txl_meta_input($post->ID, 'end_date', 'end date', 'date');
	echo txl_meta_input($post->ID, 'booking_date', 'booked on', 'date');
	echo txl_meta_input($post->ID, 'total', 'Total rent', 'number');
	echo txl_meta_input($post->ID, 'paid', 'paid', 'number');
	echo txl_meta_input($post->ID, 'due', 'due', 'number');
	echo '</table>';
	
}

function txl_meta_input ($postid, $name,$description, $type){
	$value=get_post_meta( $postid, $name , true );
	
	if (substr($name, -5)=='_date'){
		if ($value<1){
			//$value=date('Ymd');
			$value=strtotime('now');
		}
		$value=date('j-n-Y',$value);
	}
	
	return ('<tr><td><label for="'.$name.'">'.$description.': </label></td><td><input name="'.$name.'" value="'.$value.'" type="'.$type.'" step="0.01"></td></tr>');
}

function txl_save ($postid, $name){

	if ( !isset( $_POST[name] )) {return;}
	
	$value = sanitize_text_field( $_POST[$name] );
	if (substr($name, -5)=='_date'){
		$value=strtotime($value);
	}
	update_post_meta( $postid, $name, $value );
}

function save_booking_meta($postid){
	if ( get_post_type($postid) != 'booking' ) {return;}
	if ( ! current_user_can( 'edit_post', $postid ) ) {return;}
	
	$txl_fields=array('name', 'email', 'start_date', 'end_date','booking_date', 'total', 'paid', 'due'); 
	foreach ( $txl_fields as $field ) {
		txl_save($postid, $field);
	}
}

add_action( 'pre_post_update', 'save_booking_meta' ); 




function flip($arr)
{
	if (is_array($arr)){
		$out = array();
		foreach ($arr as $key => $subarr)
		{
				foreach ($subarr as $subkey => $subvalue)
				{
					 $out[$subkey][$key] = $subvalue;
				}
		}
		return $out;
	}
}

function makedecimals($in, $decimals){
	return number_format((float)$in, $decimals, '.', '');
}
