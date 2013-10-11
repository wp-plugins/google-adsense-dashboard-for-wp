<?php
/* 
Plugin Name: Google Adsense Dashboard
Plugin URI: http://www.deconf.com
Description: Earnings Dashboard will display Google Adsense earnings and statistics into Admin Dashboard. 
Author: Alin Marcu
Version: 2.1 
Author URI: http://www.deconf.com
*/  

function gads_dash_admin() {  
    include('gads_dash_admin.php');  
} 

function gads_dash_init() {
  	load_plugin_textdomain( 'gads-dash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
	
function gads_dash_admin_actions() {
	if (current_user_can('manage_options')) {  
		add_options_page("Earnings Dashboard", "Earnings Dashboard", "manage_options", "Earnings_Dashboard", "gads_dash_admin");     
	}	
}  

$plugin = plugin_basename(__FILE__);  
add_action('admin_menu', 'gads_dash_admin_actions'); 
add_action( 'wp_dashboard_setup', 'gads_dash_setup' );
add_action('admin_enqueue_scripts', 'gads_dash_admin_enqueue_scripts');
add_action('plugins_loaded', 'gads_dash_init');
add_filter("plugin_action_links_$plugin", 'gads_dash_settings_link' );

function gads_dash_admin_enqueue_scripts() {
	if (get_option('gads_dash_style')=="green"){
		wp_register_style( 'gads_dash', plugins_url('gads_dash.css', __FILE__) );
		wp_enqueue_style( 'gads_dash' );
	} else{
		wp_register_style( 'gads_dash', plugins_url('gads_dash_light.css', __FILE__) );
		wp_enqueue_style( 'gads_dash' );
	}	
}

function gads_dash_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=Earnings_Dashboard">'.__("Settings",'gads-dash').'</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

function gads_dash_setup() {
	if (current_user_can(get_option('gads_dash_access'))) {
		wp_add_dashboard_widget(
			'gads-dash-widget',
			'Earnings Dashboard for Google Adsense™',
			'gads_dash_content',
			$control_callback = null
		);
	}
}

function gads_dash_content() {

	require_once 'functions.php';
	
	$auth = new AdSenseAuth();
	
	$result=$auth->authenticate('default');
	
	$adSense=$auth->getAdSenseService();

	if(isset($_REQUEST['query_adsense']))
		$query_adsense = $_REQUEST['query_adsense'];
	else	
		$query_adsense = "EARNINGS";
		
	if(isset($_REQUEST['period_adsense']))	
		$period_adsense = $_REQUEST['period_adsense'];
	else
		$period_adsense = "last30days"; 	

	if (get_option('gads_dash_style')=="light"){ 
		$css="colors:['gray','darkgray'],";
		$colors="black";
	} else{
		$css="colors:['green','darkgreen'],";
		$colors="green";
	}		
		
	switch ($period_adsense){

		case 'today'	:	$from = date('Y-m-d'); 
							$to = date('Y-m-d');
							break;

		case 'yesterday'	:	$from = date('Y-m-d', time()-24*60*60);
								$to = date('Y-m-d', time()-24*60*60);
								break;
		
		case 'last7days'	:	$from = date('Y-m-d', time()-7*24*60*60);
							$to = date('Y-m-d', time()-24*60*60);
							break;	

		case 'last14days'	:	$from = date('Y-m-d', time()-14*24*60*60);
							$to = date('Y-m-d', time()-24*60*60);
							break;	
							
		default	:	$from = date('Y-m-d', time()-30*24*60*60);
					$to = date('Y-m-d', time()-24*60*60);
					break;

	}

	switch ($query_adsense){

		case 'COST_PER_CLICK'	:	$title="Cost per Click"; break;

		case 'CLICKS'	:	$title="Clicks"; break;	

		case 'PAGE_VIEWS'	:	$title="Page Views"; break;

		case 'PAGE_VIEWS_CTR'	:	$title="Click Through Rate"; break;
		
		case 'PAGE_VIEWS_RPM'	:	$title="Revenue per thousand impressions"; break;			
		
		default	:	$title="Earnings";

	}
	
	$optParams = array(
	  'metric' => array($query_adsense),
	  'dimension' => 'DATE',
	  'sort' => 'DATE',
	  'useTimezoneReporting' => get_option('gads_dash_timezone')
	);
	try{
	
		$serial='gadsdash_qr1'.str_replace(array(',','-',date('Y')),"",$from.$to.get_option('gads_dash_timezone').$query_adsense);
		$transient = get_transient($serial);
		if ( empty( $transient ) ){
		$data = $adSense->reports->generate($from, $to, $optParams);
		set_transient( $serial, $data, get_option('gads_dash_cachetime'));
		}else{
			$data = $transient;		
		}	
	
	} catch(exception $e) {
		if (get_option('gads_dash_token')){
			echo gads_dash_pretty_error($e);
			return;
		}
	}
	
	if (!isset($data)){
		return;
	}
	
	$gads_chart1_data="";
	for ($i=0;$i<$data['totalMatchedRows'];$i++){
		if ($query_adsense=='PAGE_VIEWS_CTR')
			$gads_chart1_data.="['".$data['rows'][$i][0]."',".($data['rows'][$i][1]*100)."],";
		else
			$gads_chart1_data.="['".$data['rows'][$i][0]."',".$data['rows'][$i][1]."],";

	}
	$gads_chart1_data=rtrim($gads_chart1_data,',');
	
	$optParams = array(
	  'metric' => array(
		'EARNINGS', 'COST_PER_CLICK', 'CLICKS', 'PAGE_VIEWS', 'PAGE_VIEWS_CTR',  'PAGE_VIEWS_RPM'
	  ),
	  'dimension' => 'YEAR',
	  'useTimezoneReporting' => get_option('gads_dash_timezone')
	);
	
	try{
		$serial='gadsdash_qr2'.str_replace(array(',','-',date('Y')),"",$from.$to.get_option('gads_dash_timezone'));
		$transient = get_transient($serial);
		if ( empty( $transient ) ){
			$gads_sum_data = $adSense->reports->generate($from, $to, $optParams);
			set_transient( $serial, $gads_sum_data, get_option('gads_dash_cachetime'));
		}else{
			$gads_sum_data = $transient;		
		}	
	}  catch(exception $e) {
		if (get_option('gads_dash_token')){
			echo gads_dash_pretty_error($e);
			return;
		}
	}

// Channel Performance

	$optParams = array(
	  'metric' => array(
		'EARNINGS', 'COST_PER_CLICK', 'CLICKS', 'PAGE_VIEWS', 'PAGE_VIEWS_CTR',  'PAGE_VIEWS_RPM'
	  ),
	  'dimension' => 'CUSTOM_CHANNEL_NAME',
	  'sort' => '-EARNINGS',
	  'useTimezoneReporting' => get_option('gads_dash_timezone')
	);
	try{
	
		$serial='gadsdash_qr3'.str_replace(array(',','-',date('Y')),"",$from.$to.get_option('gads_dash_timezone'));
		$transient = get_transient($serial);
		if ( empty( $transient ) ){
		$chdata = $adSense->reports->generate($from, $to, $optParams);
		set_transient( $serial, $chdata, get_option('gads_dash_cachetime'));
		}else{
			$chdata = $transient;		
		}	
	
	} catch(exception $e) {
		if (get_option('gads_dash_token')){
			echo gads_dash_pretty_error($e);
			return;
		}
	}	
	//print_r($chdata);
	$gads_ch_data="";
	for ($i=0;$i<$chdata['totalMatchedRows'];$i++){
		$validate_rpm=($chdata['rows'][$i][6]=="")?0.00:$chdata['rows'][$i][6];
		$gads_ch_data.="['".$chdata['rows'][$i][0]."',".$chdata['rows'][$i][1].",".$chdata['rows'][$i][2].",".$chdata['rows'][$i][3].",".$chdata['rows'][$i][4].",".($chdata['rows'][$i][5]*100).",".$validate_rpm."],";
	}
	$gads_ch_data=rtrim($gads_ch_data,',');
	
// Ads Performance	

	$optParams = array(
	  'metric' => array(
		'EARNINGS', 'COST_PER_CLICK', 'CLICKS', 'AD_REQUESTS', 'AD_REQUESTS_CTR', 'AD_REQUESTS_RPM'
	  ),
	  'dimension' => 'AD_UNIT_NAME',
	  'sort' => '-EARNINGS',
	  'useTimezoneReporting' => get_option('gads_dash_timezone')
	);
	try{
	
		$serial='gadsdash_qr4'.str_replace(array(',','-',date('Y')),"",$from.$to.get_option('gads_dash_timezone'));
		$transient = get_transient($serial);
		if ( empty( $transient ) ){
		$adsdata = $adSense->reports->generate($from, $to, $optParams);
		set_transient( $serial, $adsdata, get_option('gads_dash_cachetime'));
		}else{
			$adsdata = $transient;		
		}	
	
	} catch(exception $e) {
		if (get_option('gads_dash_token')){
			echo gads_dash_pretty_error($e);
			return;
		}
	}	
	//print_r($adsdata);
	$gads_ads_data="";
	for ($i=0;$i<$adsdata['totalMatchedRows'];$i++){
		$gads_ads_data.="['".$adsdata['rows'][$i][0]."',".$adsdata['rows'][$i][1].",".$adsdata['rows'][$i][2].",".$adsdata['rows'][$i][3].",".$adsdata['rows'][$i][4].",".($adsdata['rows'][$i][5]*100).",".$adsdata['rows'][$i][6]."],";
	}
	$gads_ads_data=rtrim($gads_ads_data,',');
	
	$code='<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
	  google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(gads_dash_callback);

	  function gads_dash_callback(){
			adsense_drawChart1();
			if(typeof gads_dash_channels == "function"){
				gads_dash_channels();
			}
			if(typeof gads_dash_ads == "function"){
				gads_dash_ads();
			}			
	  }
		  
	  function adsense_drawChart1() {
		var data = google.visualization.arrayToDataTable(['."
		  ['Date', '".$title."'],"
		  .$gads_chart1_data.
		"  
		]);

		var options = {
		  legend: {position: 'none'},	
		  pointSize: 3,".$css."
		  title: '".$title."',titleTextStyle: {color: '#000000'},
		  chartArea: {width: '85%'},
		  hAxis: { title: 'Date',  titleTextStyle: {color: '".$colors."'}, showTextEvery: 5}
		};

		var chart = new google.visualization.AreaChart(document.getElementById('adsense_chart1_div'));
		chart.draw(data, options);
		
	  }";
	  
	if (get_option('gads_dash_channels')){
		
		if ($gads_ch_data){
		 $code.='
			google.load("visualization", "1", {packages:["table"]})
			function gads_dash_channels() {
			var data = google.visualization.arrayToDataTable(['."
			  ['".__("Channel",'gads-dash')."', '".__("Earnings",'gads-dash')."', '".__("CPC",'gads-dash')."', '".__("Clicks",'gads-dash')."', '".__("Views",'gads-dash')."', '".__("CTR",'gads-dash')."', '".__("RPM",'gads-dash')."'],"
			  .$gads_ch_data.
			"  
			]);
			
			var options = {
				page: 'enable',
				pageSize: 5,
				width: '100%'
			};        
			
			var chart = new google.visualization.Table(document.getElementById('gads_dash_channels'));
			chart.draw(data, options);
			
		  }";
		}
	}

	if (get_option('gads_dash_ads')){
		
		if ($gads_ads_data){
		 $code.='
			google.load("visualization", "1", {packages:["table"]})
			function gads_dash_ads() {
			var data = google.visualization.arrayToDataTable(['."
			  ['".__("Unit Name",'gads-dash')."', '".__("Earnings",'gads-dash')."', '".__("CPC",'gads-dash')."', '".__("Clicks",'gads-dash')."', '".__("Requests",'gads-dash')."', '".__("CTR",'gads-dash')."', '".__("RPM",'gads-dash')."'],"
			  .$gads_ads_data.
			"  
			]);
			
			var options = {
				page: 'enable',
				pageSize: 5,
				width: '100%'
			};        
			
			var chart = new google.visualization.Table(document.getElementById('gads_dash_ads'));
			chart.draw(data, options);
			
		  }";
		}
	}	
	  
	$code.="</script>".'
	<div id="gads-dash">
	<center>
		<div id="buttons_div_adsense">
		<center>
			<input class="gadsbutton" type="button" value="'.__("Today",'gads-dash').'" onClick="window.location=\'?period_adsense=today&query_adsense='.$query_adsense.'\'" />
			<input class="gadsbutton" type="button" value="'.__("Yesterday",'gads-dash').'" onClick="window.location=\'?period_adsense=yesterday&query_adsense='.$query_adsense.'\'" />
			<input class="gadsbutton" type="button" value="'.__("Last 7 Days",'gads-dash').'" onClick="window.location=\'?period_adsense=last7days&query_adsense='.$query_adsense.'\'" />
			<input class="gadsbutton" type="button" value="'.__("Last 14 Days",'gads-dash').'" onClick="window.location=\'?period_adsense=last14days&query_adsense='.$query_adsense.'\'" />
			<input class="gadsbutton" type="button" value="'.__("Last 30 Days",'gads-dash').'" onClick="window.location=\'?period_adsense=last30days&query_adsense='.$query_adsense.'\'" />
		</center>
		</div>
		
		<div id="adsense_chart1_div"></div>
		
		<div id="adsense_details_div">
			<table class="adsensetable" cellpadding="4">
			<tr>
			<td width=="24%">'.__("Earnings:",'gads-dash').'</td>
			<td width="12%" class="adsensevalue"><a href="?query_adsense=EARNINGS&period_adsense='.$period_adsense.'" class="adsensetable">'.$gads_sum_data['rows'][0][0].'</td>
			<td width="30%">'.__("Page Views:",'gads-dash').'</td>
			<td width="12%" class="adsensevalue"><a href="?query_adsense=PAGE_VIEWS&period_adsense='.$period_adsense.'" class="adsensetable">'.$gads_sum_data['rows'][0][3].'</a></td>
			<td width="24%">'.__("Clicks:",'gads-dash').'</td>
			<td width="12%" class="adsensevalue"><a href="?query_adsense=CLICKS&period_adsense='.$period_adsense.'" class="adsensetable">'.$gads_sum_data['rows'][0][2].'</a></td>
			</tr>
			<tr>
			<td>'.__("Cost/Click:",'gads-dash').'</td>
			<td class="adsensevalue"><a href="?query_adsense=COST_PER_CLICK&period_adsense='.$period_adsense.'" class="adsensetable">'.$gads_sum_data['rows'][0][1].'</a></td>
			<td>'.__("Click Through Rate:",'gads-dash').'</td>
			<td class="adsensevalue"><a href="?query_adsense=PAGE_VIEWS_CTR&period_adsense='.$period_adsense.'" class="adsensetable">'.($gads_sum_data['rows'][0][4]*100).'</a></td>
			<td>'.__("Revenue/Thousand:",'gads-dash').'</td>
			<td class="adsensevalue"><a href="?query_adsense=PAGE_VIEWS_RPM&period_adsense='.$period_adsense.'" class="adsensetable">'.$gads_sum_data['rows'][0][5].'</a></td>
			</tr>
			</table>
		</div>
	</center>
	</div>';
	
	if (get_option('gads_dash_channels'))
		$code .= '<br /><br /><div id="gads_dash_channels"></div>';	
	
	if (get_option('gads_dash_ads'))
		$code .= '<br /><div id="gads_dash_ads"></div>';	
		
	echo $code;
    
}	
?>