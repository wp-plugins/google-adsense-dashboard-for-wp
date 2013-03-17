<?php
/* 
Plugin Name: Google Adsense Dashboard for WP
Plugin URI: http://www.deconf.com
Description: This plugin will display Google Adsense earnings and statistics into Admin Dashboard. 
Author: Deconf.com
Version: 1.0 
Author URI: http://www.deconf.com
*/  

function gads_dash_admin() {  
    include('gads_dash_admin.php');  
} 
	
function gads_dash_admin_actions() {
	if (current_user_can('manage_options')) {  
		add_options_page("Google Adsense Dashboard", "GAds Dashboard", 1, "Google_Adsense_Dashboard", "gads_dash_admin");     
	}	
}  
  
add_action('admin_menu', 'gads_dash_admin_actions'); 
add_action( 'wp_dashboard_setup', 'gads_dash_setup' );

wp_register_style( 'gads_dash', plugins_url('gads_dash.css', __FILE__) );
wp_enqueue_style( 'gads_dash' );

function gads_dash_setup() {
	if ( current_user_can( 'manage_options' ) ) {
		wp_add_dashboard_widget(
			'gads-dash-widget',
			'Google Adsense Dashboard',
			'gads_dash_content',
			$control_callback = null
		);
	}
}

function gads_dash_content() {

	require_once 'functions.php';
	
	$auth = new AdSenseAuth();
	
	$result=$auth->authenticate('default');
	  
	//if ($result){
	//	echo $result;
	//}
	
	$adSense=$auth->getAdSenseService();


	$query_adsense = ($_REQUEST['query_adsense']=="") ? "EARNINGS" : $_REQUEST['query_adsense'];
	$period_adsense = ($_REQUEST['period_adsense']=="") ? "last30days" : $_REQUEST['period_adsense'];

	switch ($period_adsense){

		case 'today'	:	$from = date('Y-m-d'); 
							$to = date('Y-m-d');
							break;

		case 'yesterday'	:	$from = date('Y-m-d', time()-24*60*60);
								$to = date('Y-m-d', time()-24*60*60);
								break;
		
		case 'last7days'	:	$from = date('Y-m-d', time()-7*24*60*60);
							$to = date('Y-m-d');
							break;	

		case 'last14days'	:	$from = date('Y-m-d', time()-14*24*60*60);
							$to = date('Y-m-d');
							break;	
							
		default	:	$from = date('Y-m-d', time()-30*24*60*60);
					$to = date('Y-m-d');
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
	  'sort' => 'DATE'
	);
	try{
		$data = $adSense->reports->generate($from, $to, $optParams);
	}  
		catch(exception $e) {
			if (get_option('gads_dash_token')){
				echo "<br />ERROR LOG:<br /><br />".$e;
			}
			return;	
	}
	for ($i=0;$i<$data['totalMatchedRows'];$i++){
		if ($query_adsense=='PAGE_VIEWS_CTR')
			$chart1_data.="['".$data['rows'][$i][0]."',".($data['rows'][$i][1]*100)."],";
		else
			$chart1_data.="['".$data['rows'][$i][0]."',".$data['rows'][$i][1]."],";

	}

	$optParams = array(
	  'metric' => array(
		'EARNINGS', 'COST_PER_CLICK', 'CLICKS', 'PAGE_VIEWS', 'PAGE_VIEWS_CTR',  'PAGE_VIEWS_RPM'
	  ),
	  'dimension' => 'YEAR'
	);
	
	try{
		$data = $adSense->reports->generate($from, $to, $optParams);
	}  
		catch(exception $e) {
			if (get_option('gads_dash_token')){
				echo "<br />ERROR LOG:<br /><br />".$e;
			}
			return;	
	}
	$code='<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
	  google.load("visualization", "1", {packages:["corechart"]});
	  google.setOnLoadCallback(adsense_drawChart1);
		  
	  function adsense_drawChart1() {
		var data = google.visualization.arrayToDataTable(['."
		  ['Date', '".$title."'],"
		  .$chart1_data.
		"  
		]);

		var options = {
		  legend: {position: 'none'},	
		  pointSize: 3,
		  colors:['red','#004411'],
		  title: '".$title."',titleTextStyle: {color: '#000000'},
		  chartArea: {width: '80%', height: '50%'},
		  hAxis: { title: 'Date',  titleTextStyle: {color: 'red'}, showTextEvery: 5}
		};

		var chart = new google.visualization.AreaChart(document.getElementById('adsense_chart1_div'));
		chart.draw(data, options);
		
	  }

	</script>".'
	<div id="gads-dash">
	<center>
		<div id="buttons_div_adsense">
		<center>
			<input class="gadsbutton" type="button" value="Today" onClick="window.location=\'?period_adsense=today&query_adsense='.$query_adsense.'\'" />
			<input class="gadsbutton" type="button" value="Yesterday" onClick="window.location=\'?period_adsense=yesterday&query_adsense='.$query_adsense.'\'" />
			<input class="gadsbutton" type="button" value="Last 7 Days" onClick="window.location=\'?period_adsense=last7days&query_adsense='.$query_adsense.'\'" />
			<input class="gadsbutton" type="button" value="Last 14 Days" onClick="window.location=\'?period_adsense=last14days&query_adsense='.$query_adsense.'\'" />
			<input class="gadsbutton" type="button" value="Last 30 Days" onClick="window.location=\'?period_adsense=last30days&query_adsense='.$query_adsense.'\'" />
		</center>
		</div>
		
		<div id="adsense_chart1_div"></div>
		
		<div id="adsense_details_div">
			<center>
			<table class="adsensetable" cellpadding="4">
			<tr>
			<td  width=="24%">Earnings:</td>
			<td width="12%" class="adsensevalue"><a href="?query_adsense=EARNINGS&period_adsense='.$period_adsense.'" class="adsensetable">'.$data['rows'][0][0].'</td>
			<td>Page Views:</td>
			<td class="adsensevalue"><a href="?query_adsense=PAGE_VIEWS&period_adsense='.$period_adsense.'" class="adsensetable">'.$data['rows'][0][3].'</a></td>
			<td width="24%">Clicks:</td>
			<td width="12%" class="adsensevalue"><a href="?query_adsense=CLICKS&period_adsense='.$period_adsense.'" class="adsensetable">'.$data['rows'][0][2].'</a></td>
			</tr>
			<tr>
			<td width="24%">CPC:</td>
			<td width="12%" class="adsensevalue"><a href="?query_adsense=COST_PER_CLICK&period_adsense='.$period_adsense.'" class="adsensetable">'.$data['rows'][0][1].'</a></td>
			<td>CTR:</td>
			<td class="adsensevalue"><a href="?query_adsense=PAGE_VIEWS_CTR&period_adsense='.$period_adsense.'" class="adsensetable">'.($data['rows'][0][4]*100).'</a></td>
			<td>RPM:</td>
			<td class="adsensevalue"><a href="?query_adsense=PAGE_VIEWS_RPM&period_adsense='.$period_adsense.'" class="adsensetable">'.$data['rows'][0][5].'</a></td>
			</tr>
			</table>
			</center>		
		</div>
	</center>		
	</div>';

	echo $code;
    
}	
?>