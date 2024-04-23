<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require  'vendor/autoload.php';
class Oauth2callback
{

    /**
     *  constructor.
     */
    public function __construct()
    {

        require_once 'vendor/autoload.php';
        $PATH = 'client_secret.json';
        $json = file_get_contents($PATH);
        $this->client = new Google_Client();
        $this->client->setAuthConfig($PATH);
        $this->client->addScope(Google_Service_Calendar::CALENDAR);
        $this->client->addScope("https://www.googleapis.com/auth/calendar");
        $this->client->setScopes(array('https://www.googleapis.com/auth/calendar'));
        // echo'<pre>';die(print_r($_SESSION));
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']); 
        } else {
           

            if (!isset($_GET['code'])) {
                
                
                $auth_url = $this->client->createAuthUrl();
                $_SESSION['auth_url'] = $auth_url;
                // die($auth_url);
                //header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
                //https://accounts.google.com/o/oauth2/auth?response_type=code&access_type=online&client_id=358940182604-co29rvqjkg6ldajoarg7o7cd2hpan5vl.apps.googleusercontent.com&redirect_uri=https%3A%2F%2Flocalhost%2Fleads%2Flists&state&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fcalendar&approval_prompt=auto
            } else { //die("HERE");
               $this->client->authenticate($_GET['code']);
                //print_r($a); die;
                //Array ( [access_token] => ya29.A0ARrdaM8EN_o-LnNqecQ5IJUHVDUPxysqF0YqCTzUXTz6Rnw0Dd8fBP3ugoYJnJVTZFm1AlB6FVVanSIpTITXurYfPqP2TPoQ2WkUotatZujL5tP-fPsd9BIBTFox_rPB1_dzzxRkwVP3MNlwLcPUUrjQYwUF [expires_in] => 3598 [scope] => https://www.googleapis.com/auth/calendar [token_type] => Bearer [created] => 1640159798 ) 
                $_SESSION['access_token'] = $this->client->getAccessToken();
                $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/';
               // header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
            }
        }
    }

    private function getClient()
	{
		$this->tokenFile =__DIR__.'/credentials.json'; 
		
        $client = new Google_Client();
        $client->setClientId($this->ci->config->item('client_id'));
       $client->setClientSecret($this->ci->config->item('client_secret'));
       $client->setRedirectUri($this->ci->config->base_url().'auth/oauth');
        $client->setScopes(array('https://www.googleapis.com/auth/calendar'));
        $client->setApprovalPrompt('force');

		 $this->redirectUri =$this->ci->config->base_url().'auth/oauth'; 
        // Load previously authorized credentials from a file.
		
       if (file_exists($this->tokenFile)) { 
           $accessToken = file_get_contents($this->tokenFile); 
		   //echo file_exists($this->tokenFile); print_r($accessToken);exit;
       } else {
		   if (! isset($_GET['code'])) {
			  
		
    	    $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
			 $authUrl = $client->createAuthUrl(); header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));exit;
			} else {
			  $client->authenticate($_GET['code']);
			    $accessToken = $client->getAccessToken(); 
		
				header('Location: ' . filter_var(site_url(),FILTER_SANITIZE_URL));
				 if(!file_exists(dirname($this->tokenFile))) {
					mkdir(dirname($this->tokenFile), 0700, true);
				}
				 file_put_contents($this->tokenFile, json_encode($accessToken)); 
			}
     
		}
    $client->setAccessToken($accessToken);

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
    
	
}
