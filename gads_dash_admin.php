<?php

require_once 'functions.php';

if ( !current_user_can( 'manage_options' ) ) {
	return;
}
if (isset($_REQUEST['Reset'])){
	$auth = new AdSenseAuth();
	$result=$auth->gads_dash_reset_token();
	
	?><div class="updated"><p><strong><?php _e('Token Reseted.', 'gads-dash' ); ?></strong></p></div>  
	<?php
}else if(gads_dash_safe_get('gads_dash_hidden') == 'Y') {  
        //Form data sent  
        $apikey = gads_dash_safe_get('gads_dash_apikey');  
        update_option('gads_dash_apikey', sanitize_text_field($apikey));  
          
        $clientid = gads_dash_safe_get('gads_dash_clientid');  
        update_option('gads_dash_clientid', sanitize_text_field($clientid));  
          
        $clientsecret = gads_dash_safe_get('gads_dash_clientsecret');  
        update_option('gads_dash_clientsecret', sanitize_text_field($clientsecret));  
          
        $dashaccess = gads_dash_safe_get('gads_dash_access');  
        update_option('gads_dash_access', sanitize_text_field($dashaccess));  

        ?>  
        <div class="updated"><p><strong><?php _e('Options saved.', 'gads-dash' ); ?></strong></p></div>  
<?php  
    }
	
$apikey = get_option('gads_dash_apikey');  
$clientid = get_option('gads_dash_clientid');  
$clientsecret = get_option('gads_dash_clientsecret');  
$dashaccess = get_option('gads_dash_access');  
$token = get_option('gads_dash_token') ? "<font color='green'>".__("Authorized", 'gads-dash')."</font>" : "<font color='red'>".__("Not Authorized", 'gads-dash')."</font> - <i>".__("You will need to authorize the application from your Admin Dashboard", 'gads-dash')."</i>";
?>  

<div class="wrap">  
    <?php    echo "<h2>" . __( 'Google Adsense Dashboard Settings', 'gads-dash' ) . "</h2>"; ?>  
      
    <form name="gads_dash_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
        <input type="hidden" name="gads_dash_hidden" value="Y">  
        <?php    echo "<h3>" . __( 'Google Adsense Management API', 'gads-dash' ); echo " (".__("watch this", 'gads-dash')." <a href='http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/' target='_blank'>".__("Step by step video tutorial", 'gads-dash')."</a>)"."</h3>"; ?>  
        <p><?php echo "<b>".__("API Key:", 'gads-dash' )." </b>"; ?><input type="text" name="gads_dash_apikey" value="<?php echo $apikey; ?>" size="61"><?php echo "<i> ex: ".__("AIzaSyASK7dLaii4326AZVyZ6MCOIQOY6F30G_1", 'gads-dash')."</i>"; ?></p>  
        <p><?php echo "<b>".__("Client ID:", 'gads-dash' )." </b>"; ?><input type="text" name="gads_dash_clientid" value="<?php echo $clientid; ?>" size="60"><?php echo "<i> ex: ".__("111342334706.apps.googleusercontent.com", 'gads-dash' )."</i>"; ?></p>  
        <p><?php echo "<b>".__("Client Secret:", 'gads-dash' )." </b>"; ?><input type="text" name="gads_dash_clientsecret" value="<?php echo $clientsecret; ?>" size="55"><?php echo "<i> ex: ".__("c62POy23C_2qK5fd3fdsec2o", 'gads-dash' )."</i>"; ?></p>  
		<p><?php echo "<b>".__("Application Status:", 'gads-dash' )." </b>"; echo $token; ?></p>  
		<?php echo "<h3>" . __( 'Access Level', 'gads-dash' ). "</h3>";?>
		<p><?php _e("View Access Level: ", 'gads-dash' ); ?>
		<select id="gads_dash_access" name="gads_dash_access">
			<option value="manage_options" <?php if (($dashaccess=="manage_options") OR (!$dashaccess)) echo "selected='yes'"; echo ">".__("Administrators", 'gads-dash');?></option>
			<option value="edit_pages" <?php if ($dashaccess=="edit_pages") echo "selected='yes'"; echo ">".__("Editors", 'gads-dash');?></option>
			<option value="publish_posts" <?php if ($dashaccess=="publish_posts") echo "selected='yes'"; echo ">".__("Authors", 'gads-dash');?></option>
			<option value="edit_posts" <?php if ($dashaccess=="edit_posts") echo "selected='yes'"; echo ">".__("Contributors", 'gads-dash');?></option>
		</select></p>
        <p class="submit">  
        <input type="submit" name="Submit" class="button button-primary" value="<?php _e('Update Options', 'gads-dash' ) ?>" />
		<input type="submit" name="Reset" class="button button-primary" value="<?php _e('Reset Token', 'gads-dash' ) ?>" />		
        </p>
    </form>  
</div> 