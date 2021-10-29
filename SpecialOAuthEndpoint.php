<?php

use \Salesforce\OAuthConfig;
use \Salesforce\OAuth;
use \Salesforce\OAuthRequest;
use \Salesforce\RestApiRequest;


require("config/config.php");

class SpecialOAuthEndpoint extends SpecialPage {

    private $config;

    private $accessToken;

    private $instanceUrl;

    public function __construct() {

        global $oauth_config;

        $this->config = new OAuthConfig($oauth_config);

        parent::__construct("OAuthEndpoint");
    }


    public function execute($parameter) {

        global $wgUser, $wgScriptPath, $wgRequest;

        if(empty($_GET["code"])) {

            $_SESSION["redirect"] = $_SERVER["PHP_SELF"];
    
            if(!$this->userIsAuthorized()){
        
                $response = OAuth::newOAuthResponse($this->config, "webserver");

                $url = $response->getHeader("Location")->getValue();

                // Redirect to the salesforce login page.
                header("Location: $url");
            }

        } else {

            $this->config->setAuthorizationCode($_GET["code"]);
            unset($_GET["code"]);
        
            // Build the request and send the authorization code returned in the previous step.
            $oauth = OAuthRequest::newAccessTokenRequest($this->config, "webserver");
        
            $resp = $oauth->authorize();
        
            $this->accessToken = $resp->getAccessToken();
            $this->instanceUrl = $resp->getInstanceUrl();

            $sfUserInfo = $this->getUserInfo();

            $newWikiUser = $this->getNewWikiUser($sfUserInfo);

            $session = $wgRequest->getSession();
            $session->persist();
            $this->getContext()->setUser($newWikiUser);

            $wgUser = $wikiUser;

            //header("Location: $wgScriptPath/index.php/Main_Page");
		}
    }



    public function userIsAuthorized(){
    
        return $_SESSION["authorized"] == True;
    }


    public function getNewWikiUser($userInfo) {

        $firstName = $userInfo["given_name"];
        $lastName = $userInfo["family_name"];
        $username = $userInfo["preferred_username"];
        $email = $userInfo["email"];

        $wikiUser = User::createNew($username, array()); // Add the user to the database and return user object.

        if(empty($wikiUser)) throw new Exception("ERROR CREATING USER");

        $wikiUser->setEmail($email);
        $wikiUser->load();  //  loads the user based on the user's "id" field.  Specified by the "mForm" property on the user object.
        $wikiUser->setToken();  // Set the random token (used for persistent authentication) 

        return $wikiUser;
    }


    public function getUserInfo(){

		$url = "/services/oauth2/userinfo?access_token={$this->accessToken}";

		$req = new RestApiRequest($this->instanceUrl, $this->accessToken);

		$resp = $req->send($url);
		
		return $resp->getBody();
	}

}