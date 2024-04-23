<?php
if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Googleplus
{

    /**
     * Googleplus constructor.
     */
    public function __construct()
    {
        require APPPATH."../vendor/autoload.php";
        $this->client = new Google_Client();

        $this->client->setApplicationName('Portal');
        $this->ci =& get_instance();
        // $this->ci->config->load('calendar');

        $PATH = APPPATH . '../client_secret_prod.json';
        $json = file_get_contents($PATH);
        $arrData = json_decode($json, true);

        $this->client->setAuthConfigFile($PATH);

        // add scope for calendar events
        $this->client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

        // add scopes needed for oauth login
        $this->client->addScope("email");
        $this->client->addScope("profile");
        
        $this->client->setRedirectUri(site_url('login/oauth'));
        $this->client->setAccessType("offline");
        $this->client->setApprovalPrompt("force");
        $this->client->setState('');

        // always set an auth URL in session for use with JS clients
        if (!isset($_SESSION['auth_url'])) {
            $_SESSION['auth_url'] = $this->client->createAuthUrl();
        }
        return;

		if (! isset($_SESSION['code'])) {
		  $auth_url = $this->client->createAuthUrl();
		  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
		} else { 
		  $this->client->authenticate($_SESSION['code']);
		  $_SESSION['access_token'] = $this->client->getAccessToken();
         
		  $redirect_uri = site_url();
		  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
		}
        $this->client->setAccessType("offline");
        // $this->$client->setAccessToken($_SESSION['access_token']);

        // // Refresh the token if it's expired.
        // if ($this->client->isAccessTokenExpired()) {
        //     $this->client->getRefreshToken();
        // }

    
       // $this->client->addScope(Google_Service_Calendar::CALENDAR);
       /* $this->client->addScope('profile');
		// $this->client->setAccessType("offline");
		$this->client->setApprovalPrompt("force");*/
		//$this->client =  $this->getClient();
    }	
	private function getClient()
	{
		$this->tokenFile =APPPATH.'../client_secret_prod.json';
		
        $client = new Google_Client();
        $client->setClientId($this->ci->config->item('client_id'));
       $client->setClientSecret($this->ci->config->item('client_secret'));
       $client->setRedirectUri($this->ci->config->base_url().'auth/oauth');
        $client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
		 $this->redirectUri =$this->ci->config->base_url().'auth/oauth'; 
        // Load previously authorized credentials from a file.
		
       if (file_exists($this->tokenFile)) { 
           $accessToken = file_get_contents($this->tokenFile); 
		   //echo file_exists($this->tokenFile); print_r($accessToken);exit;
       } else {
		   if (! isset($_GET['code'])) {
			  
		///$client->setAuthConfig($this->tokenFile );
    	//$client->setApplicationName('Auth API');
    	$client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
			 $authUrl = $client->createAuthUrl(); header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));exit;
			} else {
			  $client->authenticate($_GET['code']);
			    $accessToken = $client->getAccessToken(); 
			//  header('Location: ' . filter_var($this->redirectUri, FILTER_SANITIZE_URL));*/
			// echo '>>>>'. $authCode = $_GET['code'];
			//	$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
				header('Location: ' . filter_var(site_url(),FILTER_SANITIZE_URL));
				 if(!file_exists(dirname($this->tokenFile))) {
					mkdir(dirname($this->tokenFile), 0700, true);
				}
				 file_put_contents($this->tokenFile, json_encode($accessToken)); 
			}
        // Request authorization from the user.
		 	/*$authUrl = $client->createAuthUrl(); header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
			if (isset($_GET['code'])) {
				$authCode = $_GET['code'];
				// Exchange authorization code for an access token.
				$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
				header('Location: ' . filter_var($this->redirectUri,FILTER_SANITIZE_URL));
				if(!file_exists(dirname($this->tokenFile))) {
					mkdir(dirname($this->tokenFile), 0700, true);
				}
				 file_put_contents($this->tokenFile, json_encode($accessToken));
			}else{
				exit('No code found');
			}*/
		}
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {

       /* // save refresh token to some variable
        $refreshTokenSaved = $client->getRefreshToken();

        // update access token
        $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

        // pass access token to some variable
        $accessTokenUpdated = $client->getAccessToken();

        // append refresh token
        $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;

        //Set the new acces token
        $accessToken = $refreshTokenSaved;
        $client->setAccessToken($accessToken);

        // save to file
        file_put_contents($this->tokenFile, json_encode($accessTokenUpdated));*/
    }
    return $client; 
	}
	
    public function loginUrl()
    {

        return $this->client->createAuthUrl();

    }

    public function getAuthenticate()
    {

        return $this->client->authenticate();

    }

    public function getAccessToken()
    {

        return $this->client->getAccessToken();

    }

    public function setAccessToken()
    {

        return $this->client->setAccessToken();

    }

    public function revokeToken()
    {

        return $this->client->revokeToken();

    }

    public function client()
    {

        return $this->client;

    }

    public function getUser()
    {

        $google_ouath = new Google_Service_Oauth2($this->client);

        return (object)$google_ouath->userinfo->get();

    }

    public function isAccessTokenExpired()
    {

        return $this->client->isAccessTokenExpired();

    }

    public function refreshToken($token){
        return $this->client->refreshToken($token);
    }
	
	public function getacc(){
		date_default_timezone_set('Asia/Dubai');
  
  $REDIRECT_URI = 'http://localhost:81';
  echo $KEY_LOCATION = __DIR__ . '/credentials.json';
  $TOKEN_FILE   = "token.txt";
  
  $SCOPES = array(
      Google_Service_Gmail::MAIL_GOOGLE_COM,
      'email',
      'profile'
  );
  
  $client = new Google_Client();
  $client->setApplicationName("ctrlq.org Application");
  $client->setAuthConfig($KEY_LOCATION);
  
  // Incremental authorization
  $client->setIncludeGrantedScopes(true);
  
  // Allow access to Google API when the user is not present. 
  $client->setAccessType('offline');
  $client->setRedirectUri($REDIRECT_URI);
  $client->setScopes($SCOPES);
  
  if (isset($_GET['code']) && !empty($_GET['code'])) {
      try {
          // Exchange the one-time authorization code for an access token
          $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
          
          // Save the access token and refresh token in local filesystem
          file_put_contents($TOKEN_FILE, json_encode($accessToken));
          
          $_SESSION['accessToken'] = $accessToken;
          header('Location: ' . filter_var($REDIRECT_URI, FILTER_SANITIZE_URL));
          exit();
      }
      catch (\Google_Service_Exception $e) {
          print_r($e);
      }
  }
  
  if (!isset($_SESSION['accessToken'])) {
      
      $token = @file_get_contents($TOKEN_FILE);
      
      if ($token == null) {
          
          // Generate a URL to request access from Google's OAuth 2.0 server:
          $authUrl = $client->createAuthUrl();
          
          // Redirect the user to Google's OAuth server
          header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
          exit();
          
      } else {
          
          $_SESSION['accessToken'] = json_decode($token, true);
          
      }
  }
  
  $client->setAccessToken($_SESSION['accessToken']);
  
  /* Refresh token when expired */
  if ($client->isAccessTokenExpired()) {
      // the new access token comes with a refresh token as well
      $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
      file_put_contents($TOKEN_FILE, json_encode($client->getAccessToken()));
  }
  
  $gmail = new Google_Service_Gmail($client);
  
  $opt_param               = array();
  $opt_param['maxResults'] = 10;
  
  $threads = $gmail->users_threads->listUsersThreads("", $opt_param);
  
  foreach ($threads as $thread) {
      print $thread->getId() . " - " . $thread->getSnippet() . '<br/>';
  }
  }

}