<?php

use \MediaWiki\MediaWikiServices;
use \MediaWiki\User\UserFactory;
use \Salesforce\OAuthConfig;
use \Salesforce\OAuth;
use \Salesforce\OAuthRequest;
use \Salesforce\RestApiRequest;


require("config/config.php");

class SpecialOAuthEndpoint extends SpecialPage {

    private $defaultRedirect = "Main_Page";

    private $userInfoEndpoint = "/services/oauth2/userinfo?access_token=";

    public function __construct() {

        parent::__construct("OAuthEndpoint");
    }


    public function execute($parameter) {

        global $oauth_config;

        $config = new OAuthConfig($oauth_config);

        if($parameter == "login"){

            $response = OAuth::newOAuthResponse($config, "webserver");

            $loginUrl = $response->getHeader("Location")->getValue();

            header("Location: $loginUrl");

            exit;
        }


        if($this->authorizationCodeGranted()) {

            $config->setAuthorizationCode($this->getRequest()->getVal("code"));

        } else {

            throw new Exception("OAUTH_ERROR: No authorization code granted");
        }

        // Build the request and send the authorization code returned in the previous step.
        $oauth = OAuthRequest::newAccessTokenRequest($config, "webserver");

        $resp = $oauth->authorize();
    
        $sfUserInfo = $this->getUserInfo($resp->getAccessToken(), $resp->getInstanceUrl());
        $username = $this->formatMWUsername($sfUserInfo["preferred_username"]);
        $email = $sfUserInfo["email"];

        $user = !$this->userExists($username) ? $this->createUser($username, $email) : $this->loadUser($username);

        $this->logUserIn($user);

        $url = $this->getRedirect();

        header("Location: $url");
    }


    public function shouldRedirectToIdentityProvider(){

        return empty($_GET["code"]);
    }

    public function authorizationCodeGranted(){

        return !empty($_GET["code"]);
    }


    public function formatMWUsername($username) {

        return ucfirst($username);
    }


    public function getRedirect() {

        global $wgScriptPath;

        $sessionRedirect = $this->getRequest()->getSessionData("redirect");

        $redirect = !empty($sessionRedirect) ? $sessionRedirect : $this->defaultRedirect;

        return "$wgScriptPath/index.php/$redirect";
    }


    public function userExists($username) {

        $userFactory = MediaWikiServices::getInstance()->getUserFactory();
        $user = $userFactory->newFromName($username);
        $user->load();

        return $user->getId() != 0;
    }


    public function loadUser($username){

        $userFactory = MediaWikiServices::getInstance()->getUserFactory();
        
        return $userFactory->newFromName($username);
    }


    public function createUser($username, $email) {

        $user = User::createNew($username, array()); // Add the user to the database and return user object.
        $user->setRealName($username);
        $user->setEmail($email);
        $user->setToken();  // Set the random token (used for persistent authentication)

        return $user;
    }


    public function logUserIn($user){

        global $wgUser;
        $user->setCookies();
        $user->saveSettings();
        $wgUser = $user;
    }


    public function getUserInfo($accessToken, $instanceUrl){

		$req = new RestApiRequest($instanceUrl, $accessToken);

		$resp = $req->send($this->userInfoEndpoint . $accessToken);
		
		return $resp->getBody();
	}
}