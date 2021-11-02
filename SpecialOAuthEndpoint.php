<?php

use \MediaWiki\MediaWikiServices;
use \MediaWiki\User\UserFactory;
use \Salesforce\OAuthConfig;
use \Salesforce\OAuth;
use \Salesforce\OAuthRequest;
use \Salesforce\RestApiRequest;


require("config/config.php");

class SpecialOAuthEndpoint extends SpecialPage {

    public function __construct() {

        parent::__construct("OAuthEndpoint");
    }


    public function execute($parameter) {

        global $wgScriptPath, $wgRequest, $wgUser, $oauth_config;

        $config = new OAuthConfig($oauth_config);

        $credentialsAccepted = !empty($_GET["code"]);

        if(!$credentialsAccepted) {

            $wgRequest->setSessionData("redirect", $_SERVER["PHP_SELF"]);
    
            if(!$this->userIsAuthorized()){
        
                $response = OAuth::newOAuthResponse($config, "webserver");

                $url = $response->getHeader("Location")->getValue();

                // Redirect to the salesforce login page.
                header("Location: $url");
            }

        } else {

            $config->setAuthorizationCode($_GET["code"]);
            unset($_GET["code"]);
        
            // Build the request and send the authorization code returned in the previous step.
            $oauth = OAuthRequest::newAccessTokenRequest($config, "webserver");
        
            $resp = $oauth->authorize();
        
            $sfUserInfo = $this->getUserInfo($resp->getAccessToken(), $resp->getInstanceUrl());
            $username = ucfirst($sfUserInfo["preferred_username"]);
            $email = $sfUserInfo["email"];

            $userFactory = MediaWikiServices::getInstance()->getUserFactory();

            $user = $userFactory->newFromName($username);
            $user->load();

            if($user->getId() == 0) {

                $user = User::createNew($username, array()); // Add the user to the database and return user object.
                $user->setRealName($username);
                $user->setEmail($email);
                $user->setToken();  // Set the random token (used for persistent authentication)
            }

            $user->setCookies();
            $user->saveSettings();
            $wgUser = $user;

            $protectedRedirect = $wgRequest->getSessionData("protected_redirect");
            $actualRedirect = !empty($protectedRedirect) ? $protectedRedirect : "Main_Page";
    
            header("Location: $wgScriptPath/index.php/$actualRedirect");
		}
    }



    public function getUserInfo($accessToken, $instanceUrl){

		$url = "/services/oauth2/userinfo?access_token={$accessToken}";

		$req = new RestApiRequest($instanceUrl, $accessToken);

		$resp = $req->send($url);
		
		return $resp->getBody();
	}


    public function userIsAuthorized(){
    
        return $_SESSION["authorized"] == True;
    }
}