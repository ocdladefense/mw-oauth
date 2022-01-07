<?php

use \MediaWiki\MediaWikiServices;
use \MediaWiki\User\UserFactory;
use \Salesforce\OAuthConfig;
use \Salesforce\OAuth;
use \Salesforce\OAuthRequest;
use \Salesforce\RestApiRequest;


class SpecialOAuthEndpoint extends SpecialPage {

    private $oauthFlow = "webserver";

    private $defaultRedirect = "Main_Page";

    private $userInfoEndpoint = "/services/oauth2/userinfo?access_token=";




    
    public function __construct() {

        parent::__construct("OAuthEndpoint");
    }


    public function execute($parameter) {

        global $oauth_config, $wgRequest;
        
        if(session_id() == '') wfSetupSession();
        
	    

        $config = new OAuthConfig($oauth_config);

        if($parameter == "login"){

            $response = OAuth::newOAuthResponse($config, $this->oauthFlow);

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
        $oauth = OAuthRequest::newAccessTokenRequest($config, $this->oauthFlow);
        $resp = $oauth->authorize();

        // Initialize some important variables to identify this user 
        // on the Salesforce platform.
        $instanceUrl = $resp->getInstanceUrl();
        $accessToken = $resp->getAccessToken();
        $userId = null;
        $contactId = null;
        

        // Run the OAuth 2.0 user query.
        $sfUserInfo = $this->getUserInfo($instanceUrl, $accessToken);
        $userId = $sfUserInfo["user_id"];
        $username = $sfUserInfo["preferred_username"];
        $email = $sfUserInfo["email"];
        $userType = $sfUserInfo["user_type"];

        // Retrieve the User.ContactId field from Salesforce.
        // NOTE: STANDARD users won't have one.
        $contactId = $this->getContactId($instanceUrl, $accessToken, $userId);


        // Oh MediaWiki... why so many functions to log the user in???
        $username = $this->formatMWUsername($username);
        $user = !$this->userExists($username) ? $this->createUser($username, $email, $userType) : $this->loadUser($username, $userType);
	    $this->getContext()->setUser($user);
        $this->logUserIn();




        $_SESSION["instance-url"] = $instanceUrl;
        $_SESSION["access-token"] = $accessToken;
        $_SESSION["sf-user-id"] = $userId; // Not currently used but let's be consistent.
        $_SESSION["sf-contact-id"] = $contactId;


        header("Location: " . $this->getRedirect());
    }



    public function getUserInfo($instanceUrl, $accessToken){

        $req = new RestApiRequest($instanceUrl, $accessToken);

        $resp = $req->send($this->userInfoEndpoint . $accessToken);
            
        return $resp->getBody();
    }


    // For STANDARD type users like membernation@ocdla.com(.ocdpartial)
    // This will return NULL.
    // What effect will this have on downstream queries that rely on there
    // being a ContactId in the MediaWiki user's session?
    public function getContactId($instanceUrl, $accessToken, $userId){
    
        $api = new RestApiRequest($instanceUrl, $accessToken);
        $query = "SELECT ContactId FROM User WHERE Id = '$userId'";
        $resp = $api->query($query);

        return $resp->getRecord()["ContactId"];
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


        $redirect = $_SESSION["redirect"] ?: $this->defaultRedirect;

        return "$wgScriptPath/$redirect";
    }


    public function userExists($username) {

        $user = User::newFromName($username);
        $user->load();

        return $user->getId() != 0;
    }


    public function loadUser($username, $userType){
        
        $user = User::newFromName($username);

        $currentGroups = $user->getGroups();
        
        // If the user is a Salesforce "STANDARD" user, add the "sysop" permission group to the user.
        if($userType == "STANDARD" && !in_array("sysop", $currentGroups)) $user->addGroup("sysop");

        return $user;
    }


    public function createUser($username, $email, $userType) {

        $user = User::createNew($username, array());
        $user->setRealName($username);
        $user->setEmail($email);

        if($userType == "STANDARD") $user->addGroup("sysop");

        $user->setToken();

        return $user;
    }


    public function logUserIn(){

        global $wgUser;

	    $user = $this->getUser();
        $user->setCookies();
        $user->saveSettings();
        $wgUser = $user;
    }

}