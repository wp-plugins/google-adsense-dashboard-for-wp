<?php
require_once 'functions.php';

if ( !current_user_can( 'manage_options' ) ) {
	return;
}
if (isset($_REQUEST['Clear'])){
	$auth = new AdSenseAuth();
	$auth->gads_dash_clear_cache();
	?><div class="updated"><p><strong><?php _e('Cleared Cache.', 'gads-dash' ); ?></strong></p></div>  
	<?php
}
if (isset($_REQUEST['Reset'])){
	$auth = new AdSenseAuth();
	$auth->gads_dash_reset_token();
	?><div class="updated"><p><strong><?php _e('Token Reseted.', 'gads-dash'); ?></strong></p></div>  
	<?php
}else if(gads_dash_safe_get('gads_dash_hidden') == 'Y') {  
        //Form data sent  
        $apikey = gads_dash_safe_get('gads_dash_apikey');  
        if ($apikey){
			update_option('gads_dash_apikey', sanitize_text_field($apikey));  
        }
		
        $clientid = gads_dash_safe_get('gads_dash_clientid');
        if ($clientid){		
			update_option('gads_dash_clientid', sanitize_text_field($clientid));  
        }
		
        $clientsecret = gads_dash_safe_get('gads_dash_clientsecret');  
        if ($clientsecret){			
			update_option('gads_dash_clientsecret', sanitize_text_field($clientsecret));  
		}
		
        $dashaccess = gads_dash_safe_get('gads_dash_access');  
        update_option('gads_dash_access', $dashaccess);
		
		$gads_dash_channels = gads_dash_safe_get('gads_dash_channels');
		update_option('gads_dash_channels', $gads_dash_channels);
		
		$gads_dash_ads = gads_dash_safe_get('gads_dash_ads');
		update_option('gads_dash_ads', $gads_dash_ads);		

		$gads_dash_style = gads_dash_safe_get('gads_dash_style');
		update_option('gads_dash_style', $gads_dash_style);
		
		$gads_dash_cachetime = gads_dash_safe_get('gads_dash_cachetime');
		update_option('gads_dash_cachetime', $gads_dash_cachetime);
		
		$gads_dash_timezone = gads_dash_safe_get('gads_dash_timezone');
		update_option('gads_dash_timezone', $gads_dash_timezone);		

		$gads_dash_userapi = gads_dash_safe_get('gads_dash_userapi');
		update_option('gads_dash_userapi', $gads_dash_userapi);			
		
		if (!isset($_REQUEST['Clear']) AND !isset($_REQUEST['Reset'])){
			?>  
			<div class="updated"><p><strong><?php _e('Options saved.', 'gads-dash'); ?></strong></p></div>  
			<?php
		}
    }else if(gads_dash_safe_get('gads_dash_hidden') == 'A') {
        $apikey = gads_dash_safe_get('gads_dash_apikey');  
        if ($apikey){
			update_option('gads_dash_apikey', sanitize_text_field($apikey));  
        }
		
        $clientid = gads_dash_safe_get('gads_dash_clientid');
        if ($clientid){		
			update_option('gads_dash_clientid', sanitize_text_field($clientid));  
        }
		
        $clientsecret = gads_dash_safe_get('gads_dash_clientsecret');  
        if ($clientsecret){			
			update_option('gads_dash_clientsecret', sanitize_text_field($clientsecret));  
		}

		$gads_dash_userapi = gads_dash_safe_get('gads_dash_userapi');
		update_option('gads_dash_userapi', $gads_dash_userapi);			
	}
	
if (isset($_REQUEST['Authorize'])){
	$adminurl = admin_url("#gads-dash-widget");
	echo '<script> window.location="'.$adminurl.'"; </script> ';
}
	
if(!get_option('gads_dash_access')){
	update_option('gads_dash_access', "manage_options");	
}

if(!get_option('gads_dash_style')){
	update_option('gads_dash_style', "green");	
}

$apikey = get_option('gads_dash_apikey');  
$clientid = get_option('gads_dash_clientid');  
$clientsecret = get_option('gads_dash_clientsecret');  
$dashaccess = get_option('gads_dash_access'); 
$gads_dash_channels = get_option('gads_dash_channels');
$gads_dash_ads = get_option('gads_dash_ads');
$gads_dash_style = get_option('gads_dash_style');
$gads_dash_cachetime = get_option('gads_dash_cachetime');
$gads_dash_timezone = get_option('gads_dash_timezone');
$gads_dash_userapi = get_option('gads_dash_userapi');

if ( is_rtl() ) {
	$float_main="right";
	$float_note="left";
}else{
	$float_main="left";
	$float_note="right";	
}

?>  
<div class="wrap">
<div style="width:70%;float:<?php echo $float_main; ?>;">  
    <?php echo "<h2>" . __( 'Earnings Dashboard Settings', 'gads-dash' ) . "</h2>"; ?>  
        <form name="gads_dash_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
		<?php echo "<h3>". __( 'Google Adsense API', 'gads-dash' )."</h3>"; ?>  
        <?php echo "<i>".__("You should watch this", 'gads-dash')." <a href='http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/' target='_blank'>". __("Step by step video tutorial")."</a> ".__("before proceeding to authorization", 'gads-dash').". ".__("To authorize this application using our API Project, press the", 'gads_dash')." <b>".__("Authorize Application", 'gads-dash')."</b> ".__(" button. If you want to authorize it using your own API Project, check the option bellow and enter your project credentials before pressing the", 'gads-dash')." <b>".__("Authorize Application", 'gads-dash')."</b> ".__("button.", 'gads-dash')."</i>";?>
		<p><input name="gads_dash_userapi" type="checkbox" id="gads_dash_userapi" onchange="this.form.submit()" value="1"<?php if (get_option('gads_dash_userapi')) echo " checked='checked'"; ?>  /><?php echo "<b>".__(" use your own API Project credentials", 'gads-dash' )."</b>"; ?></p>
		<?php
		if (get_option('gads_dash_userapi')){?>
			<p><?php echo "<b>".__("API Key:", 'gads-dash')." </b>"; ?><input type="text" name="gads_dash_apikey" value="<?php echo $apikey; ?>" size="61"></p>  
			<p><?php echo "<b>".__("Client ID:", 'gads-dash')." </b>"; ?><input type="text" name="gads_dash_clientid" value="<?php echo $clientid; ?>" size="60"></p>  
			<p><?php echo "<b>".__("Client Secret:", 'gads-dash')." </b>"; ?><input type="text" name="gads_dash_clientsecret" value="<?php echo $clientsecret; ?>" size="55"></p>  
			<?php echo "<i>".__("Old users should also follow this", 'gads-dash')." <a href='http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/' target='_blank'>". __("step by step video tutorial")."</a> ".__(", there are some major changes in this version, if you want to use your own API Project, you should delete your old API Project and create a new one!", 'gads-dash')."</i>";?>
		<?php }?>
		<p><?php 
			if (get_option('gads_dash_token')){
				echo "<input type=\"submit\" name=\"Reset\" class=\"button button-primary\" value=\"".__("Clear Authorization", 'gads-dash')."\" />";
				?> <input type="submit" name="Clear" class="button button-primary" value="<?php _e('Clear Cache', 'gads-dash' ) ?>" /><?php		
				echo '<input type="hidden" name="gads_dash_hidden" value="Y">';  
			} else{
				echo "<input type=\"submit\" name=\"Authorize\" class=\"button button-primary\" value=\"".__("Authorize Application", 'gads-dash')."\" />";
				?> <input type="submit" name="Clear" class="button button-primary" value="<?php _e('Clear Cache', 'gads-dash' ) ?>" /><?php
				echo '<input type="hidden" name="gads_dash_hidden" value="A">';
				echo "</form>";
				_e("(the rest of the settings will show up after completing the authorization process)", 'gads-dash' );
				echo "</div>";
				?>
				<div class="note" style="float:<?php echo $float_note; ?>;text-align:<?php echo $float_main; ?>;"> 
						<center>
							<h3><?php _e("Setup Tutorial",'gads-dash') ?></h3>
							<a href="http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/" target="_blank"><img src="../wp-content/plugins/google-adsense-dashboard-for-wp/img/video-tutorial.png" width="95%" /></a>
						</center>
						<center>
							<br /><h3><?php _e("Support Links",'gads-dash') ?></h3>
						</center>			
						<ul>
							<li><a href="http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/" target="_blank"><?php _e("Earnings Dashboard Official Page",'gads-dash') ?></a></li>
							<li><a href="http://wordpress.org/support/plugin/google-adsense-dashboard-for-wp" target="_blank"><?php _e("Earnings Dashboard Wordpress Support",'gads-dash') ?></a></li>
							<li><a href="http://forum.deconf.com/en/wordpress-plugins-f182/" target="_blank"><?php _e("Earnings Dashboard on Deconf Forum",'gads-dash') ?></a></li>			
						</ul>
						<center>
							<br /><h3><?php _e("Useful Plugins",'gads-dash') ?></h3>
						</center>			
						<ul>
							<li><a href="http://www.deconf.com/en/projects/youtube-analytics-dashboard-for-wordpress/" target="_blank"><?php _e("YouTube Analytics Dashboard",'gads-dash') ?></a></li>
							<li><a href="http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/" target="_blank"><?php _e("Google Analytics Dashboard",'gads-dash') ?></a></li>
							<li><a href="http://www.deconf.com/en/projects/clicky-analytics-plugin-for-wordpress/" target="_blank"><?php _e("Clicky Analytics",'gads-dash') ?></a></li>						
							<li><a href="http://wordpress.org/extend/plugins/follow-us-box/" target="_blank"><?php _e("Follow Us Box",'gads-dash') ?></a></li>			
						</ul>				
				</div></div><?php				
				return;
			} ?>
		</p>  
		<?php echo "<h3>" . __( 'Access Level', 'gads-dash' ). "</h3>";?>
		<p><?php _e("View Access Level: ", 'gads-dash' ); ?>
		<select id="gads_dash_access" name="gads_dash_access">
			<option value="manage_options" <?php if (($dashaccess=="manage_options") OR (!$dashaccess)) echo "selected='yes'"; echo ">".__("Administrators", 'gads-dash');?></option>
			<option value="edit_pages" <?php if ($dashaccess=="edit_pages") echo "selected='yes'"; echo ">".__("Editors", 'gads-dash');?></option>
			<option value="publish_posts" <?php if ($dashaccess=="publish_posts") echo "selected='yes'"; echo ">".__("Authors", 'gads-dash');?></option>
			<option value="edit_posts" <?php if ($dashaccess=="edit_posts") echo "selected='yes'"; echo ">".__("Contributors", 'gads-dash');?></option>
		</select></p>

		<?php echo "<h3>" . __( 'Additional Settings', 'gads-dash' ). "</h3>";?>
		<p><input name="gads_dash_channels" type="checkbox" id="gads_dash_channels" value="1"<?php if (get_option('gads_dash_channels')) echo " checked='checked'"; ?>  /><?php _e(" show Custom Channels performance report", 'gads-dash' ); ?></p>
		<p><input name="gads_dash_ads" type="checkbox" id="gads_dash_ads" value="1"<?php if (get_option('gads_dash_ads')) echo " checked='checked'"; ?>  /><?php _e(" show Ad Units performance report", 'gads-dash' ); ?></p>
		<p><?php _e("CSS Settings: ", 'gads-dash' ); ?>
		<select id="gads_dash_style" name="gads_dash_style">
			<option value="green" <?php if (($gads_dash_style=="green") OR (!$gads_dash_style)) echo "selected='yes'"; echo ">".__("Green Theme", 'gads-dash');?></option>
			<option value="light" <?php if ($gads_dash_style=="light") echo "selected='yes'"; echo ">".__("Light Theme", 'gads-dash');?></option>
		</select></p>
		<?php echo "<h3>" . __( 'Cache Settings', 'gads-dash' ). "</h3>";?>
		<p><?php _e("Cache Time: ", 'gads-dash' ); ?>
		<select id="gads_dash_cachetime" name="gads_dash_cachetime">
			<option value="900" <?php if ($gads_dash_cachetime=="900") echo "selected='yes'"; echo ">".__("15 minutes", 'gads-dash');?></option>
			<option value="1800" <?php if ($gads_dash_cachetime=="1800") echo "selected='yes'"; echo ">".__("30 minutes", 'gads-dash');?></option>
			<option value="3600" <?php if (($gads_dash_cachetime=="3600") OR (!$gads_dash_cachetime)) echo "selected='yes'"; echo ">".__("1 hour", 'gads-dash');?></option>
			<option value="7200" <?php if ($gads_dash_cachetime=="7200") echo "selected='yes'"; echo ">".__("2 hours", 'gads-dash');?></option>
		</select></p>

		<?php echo "<h3>" . __( 'Google Adsense Time Zone', 'gads-dash' ). "</h3>";?>

		<p><?php _e("Time Zone: ", 'gads-dash' ); ?>
		<select id="gads_dash_timezone" name="gads_dash_timezone">
			<option value="false" <?php if (($gads_dash_timezone=="false") OR (!$gads_dash_timezone)) echo "selected='yes'"; echo ">".__("Billing time zone (PST)", 'gads-dash');?></option>
			<option value="true" <?php if ($gads_dash_timezone=="true") echo "selected='yes'"; echo ">".__("Account time zone", 'gads-dash');?></option>
		</select>
		</p>

		<p class="submit">  
        <input type="submit" name="Submit" class="button button-primary" value="<?php _e('Update Options', 'gads-dash' ) ?>" />
        </p>  
    </form>  
</div>
<div class="note" style="float:<?php echo $float_note; ?>;text-align:<?php echo $float_main; ?>;"> 
		<center>
			<h3><?php _e("Setup Tutorial",'gads-dash') ?></h3>
			<a href="http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/" target="_blank"><img src="../wp-content/plugins/google-adsense-dashboard-for-wp/img/video-tutorial.png" width="95%" /></a>
		</center>
		<center>
			<br /><h3><?php _e("Support Links",'gads-dash') ?></h3>
		</center>			
		<ul>
			<li><a href="http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/" target="_blank"><?php _e("Earnings Dashboard - Official Page",'gads-dash') ?></a></li>
			<li><a href="http://wordpress.org/support/plugin/google-adsense-dashboard-for-wp" target="_blank"><?php _e("Earnings Dashboard - Wordpress Support",'gads-dash') ?></a></li>
			<li><a href="http://forum.deconf.com/en/wordpress-plugins-f182/" target="_blank"><?php _e("Earnings Dashboard on Deconf Forum",'gads-dash') ?></a></li>			
		</ul>
		<center>
			<br /><h3><?php _e("Useful Plugins",'gads-dash') ?></h3>
		</center>			
		<ul>
			<li><a href="http://www.deconf.com/en/projects/youtube-analytics-dashboard-for-wordpress/" target="_blank"><?php _e("YouTube Analytics Dashboard",'gads-dash') ?></a></li>
			<li><a href="http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/" target="_blank"><?php _e("Google Analytics Dashboard",'gads-dash') ?></a></li>
			<li><a href="http://www.deconf.com/en/projects/clicky-analytics-plugin-for-wordpress/" target="_blank"><?php _e("Clicky Analytics",'gads-dash') ?></a></li>						
			<li><a href="http://wordpress.org/extend/plugins/follow-us-box/" target="_blank"><?php _e("Follow Us Box",'gads-dash') ?></a></li>			
		</ul>			
</div>
</div>