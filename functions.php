<?php

function gads_dash_safe_get($key) {
	if (array_key_exists($key, $_POST)) {
		return $_POST[$key];
	}
	return false;
}

class AdSenseAuth {
protected $apiClient;
protected $adSenseService;
private $user;

public function __construct() {
		if (!class_exists('Google_Exception')) {
			require_once 'src/Google_Client.php';
		}	
		require_once 'src/contrib/Google_AdsenseService.php';
		$scriptUri = "http://".$_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF'];
		$this->apiClient = new Google_Client();
		$this->apiClient->setClientId(get_option('gads_dash_clientid'));
		$this->apiClient->setClientSecret(get_option('gads_dash_clientsecret'));
		$this->apiClient->setDeveloperKey(get_option('gads_dash_apikey'));
		$this->apiClient->setRedirectUri($scriptUri);
		$this->adSenseService = new Google_AdSenseService($this->apiClient);	
}

	function gads_dash_store_token ($user, $token){
		update_option('gads_dash_user', $user);
		update_option('gads_dash_token', $token);
	}		
	
	function gads_dash_get_token (){

		if (get_option('gads_dash_token')){
			return get_option('gads_dash_token');
		}
		else{
			return;
		}
	
	}
	
	public function gads_dash_reset_token (){

		update_option('gads_dash_token', ""); 
	
	}
	
	function authenticate($user) {
		$this->user=$user;
		$token = $this->gads_dash_get_token();

		if (isset($token)) {
		  $this->apiClient->setAccessToken($token);
		} else {
			$this->apiClient->setScopes(array("https://www.googleapis.com/auth/adsense.readonly"));
			if (!isset($_REQUEST['authorize']) AND !isset($_REQUEST['code'])){
				if (!current_user_can('manage_options')){
					_e("Ask an admin to authorize this Application", 'gads-dash');
					return;
				}
				echo '<div style="padding:20px;"><form name="input" action="#" method="get">
				<input type="submit" class="button button-primary" name="authorize" value="Authorize Google Adsense Dashboard"/>
			</form></div>';
			}		
			else if (isset($_REQUEST['code'])) {
			  $this->apiClient->authenticate();
			  $this->gads_dash_store_token($this->user, $this->apiClient->getAccessToken());
			}
			else{
			
			  $authUrl = $this->apiClient->createAuthUrl();
			  echo '<script> window.location="'.$authUrl.'"; </script> ';
			  return;

		}

		}
	}
	
	function getAdSenseService() {
		return $this->adSenseService;
	}
	
	function gads_dash_refreshToken() {
		if ($this->apiClient->getAccessToken() != null) {
			$this->gads_dash_store_token('default', $this->apiClient->getAccessToken());
		}
	}	

}

?>