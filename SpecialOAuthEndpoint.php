<?php

use \MediaWiki\MediaWikiServices;
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

            // If there is an existing user for the given username (in the userinfo), use the existing user.  Otherwise create a new user and use the new user.
            $currentUser = !$this->getExistingUser($sfUserInfo) ? $this->getNewWikiUser($sfUserInfo) : $this->getExistingUser($sfUserInfo);

            // Setting the active user and saving it to the session.
            $wgRequest->getSession()->persist();

            $currentUser->setCookies();
            $currentUser->saveSettings();
            $wgUser = $currentUser;
    
            $this->getContext()->setUser($currentUser);
    
            header("Location: $wgScriptPath/index.php/Main_Page");
		}
    }


    // Query the database for a user with given username.  If none are found, return false;
    public function getExistingUser($userInfo) {

        $username = $userInfo["preferred_username"];

        $loadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();

        $dbConnection = $loadBalancer->getConnection(DB_REPLICA);

        $res = $dbConnection->select("user", "user_id", "user_name LIKE '%$username%' LIMIT 1");

        $object = $res->fetchObject(); // Returns false of there are no rows

        if($object == false) return $object;

        $user = User::newFromId($object->user_id);
        $user->load();  // load new user object with field data from database.

        return $user;
    }


    public function getNewWikiUser($userInfo) {

        $firstName = $userInfo["given_name"];
        $lastName = $userInfo["family_name"];
        $username = $userInfo["preferred_username"];
        $email = $userInfo["email"];

        $wikiUser = User::createNew($username, array()); // Add the user to the database and return user object.

        if(empty($wikiUser)) throw new Exception("ERROR CREATING USER:  The user name '$username' was either invalid, or already in use.");

        $wikiUser->setEmail($email);
        $wikiUser->load();  //  loads the user based on the user's "id" field.  Specified by the "mForm" property on the user object.

        if(!($wikiUser instanceof User && $wikiUser->getId())) {

			$user->addToDatabase();
			$user->confirmEmail();
		}

        $wikiUser->setToken();  // Set the random token (used for persistent authentication)

        return $wikiUser;
    }



    public function userIsAuthorized(){
    
        return $_SESSION["authorized"] == True;
    }



    public function getUserInfo($accessToken, $instanceUrl){

		$url = "/services/oauth2/userinfo?access_token={$accessToken}";

		$req = new RestApiRequest($instanceUrl, $accessToken);

		$resp = $req->send($url);
		
		return $resp->getBody();
	}

}