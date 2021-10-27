<?php

namespace MediaWiki\Extension\OAuth;

use \Salesforce\OAuthConfig;
use \Salesforce\OAuth;
use \Salesforce\OAuthRequest;
use \Salesforce\RestApiRequest;

class OAuthManager {

    public $config;

    public $flow;

    public $accessToken;

    public $instanceUrl;

    public function __construct(){

        global $oauth_config;
        
        $this->config = new OAuthConfig($oauth_config);
        $this->flow = "webserver";
    }


    public function requireAuth(){
    
        $_SESSION["redirect"] = $_SERVER["PHP_SELF"];
    
        session_write_close();
    
        if(!$this->userIsAuthorized()){
    
            $this->redirectToLogin();
        
        }
    }
    
    // Normally we determine whether the user has logged in by checking the session.
    public static function identityProviderCredentialsAccepted(){
    
        return !empty($_GET["code"]);
    }
    
    
    public function userIsAuthorized(){
    
        return $_SESSION["authorized"] == True;
    }
    
    
    public function redirectToLogin() {
    
        // If we have a webserver flow we are going to send a redirect response to the user's web browser.  The web browser redirects the user makes a request to the salesforce login page. This causes the user to be redirected to the login page.
        $response = OAuth::newOAuthResponse($this->config, $this->flow);
    
        // Get the url from the location header in the response
        $url = $response->getHeader("Location")->getValue();
    
        // Redirect to the salesforce login page.
        header("Location: $url");
    }
    
    public function requestAccessToken() {
    
        // Set the authorization code using the value in $_GET super
        $this->config->setAuthorizationCode($_GET["code"]);
    
        // Build the request and send the authorization code returned in the previous step.
        $oauth = OAuthRequest::newAccessTokenRequest($this->config, $this->flow);
    
        // Send the request
        $resp = $oauth->authorize();
    
        // The response contains the access token and instance url.
        $this->accessToken = $resp->getAccessToken();
        $this->instanceUrl = $resp->getInstanceUrl();

        return $this->accessToken;
    }


    public function getInstanceUrl() {

        return $this->instanceUrl;
    }

    public function getAccessToken() {

        return $this->accessToken;
    }


    public function getUserInfo(){

		$url = "/services/oauth2/userinfo?access_token={$this->accessToken}";

		$req = new RestApiRequest($this->instanceUrl, $this->accessToken);

		$resp = $req->send($url);
		
		return $resp->getBody();
	}
}