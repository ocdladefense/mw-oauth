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

        if(empty($_GET["code"])) {

            $_SESSION["redirect"] = $_SERVER["PHP_SELF"];
    
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
            $username = $sfUserInfo["preferred_username"];
            $email = $sfUserInfo["email"];

            $user = !$this->getExistingUser($username) ? $this->createUser($username, $email) : $this->getExistingUser($username);

            $user->setCookies();
            $user->saveSettings();
            $wgUser = $user;
    
            header("Location: $wgScriptPath/index.php/Main_Page");
		}
    }



    // Query the database for a user with given username and return an instance of "User".  If no rows are found, return false;
    public function getExistingUser($username) {

        $loadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();

        $dbConnection = $loadBalancer->getConnection(DB_REPLICA);

        $res = $dbConnection->select("user", "user_id", "user_name LIKE '%$username%' LIMIT 1");

        $object = $res->fetchObject(); // Returns standard class or false if there are no rows.

        if($object == false) return $object;

        $user = User::newFromId($object->user_id);
        $user->load();  // load new user object with field data from database. (also called by "setCookies")

        return $user;
    }


    public function createUser($username, $email) {

        $wikiUser = User::createNew($username, array()); // Add the user to the database and return user object.

        if(empty($wikiUser)) throw new Exception("ERROR CREATING USER:  The user name '$username' was either invalid, or already in use.");

        $wikiUser->setRealName($username);
        $wikiUser->setEmail($email);
        $wikiUser->setToken();  // Set the random token (used for persistent authentication)

        return $wikiUser;
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