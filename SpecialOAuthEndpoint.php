<?php

use MediaWiki\Extension\OAuth\OAuthManager;

require("config/config.php");

class SpecialOAuthEndpoint extends SpecialPage {

    public $accessToken;

    public $instanceUrl;

    public function __construct() {

        parent::__construct("OAuthEndpoint");
    }


    public function execute($parameter) {

        $mgr = new OAuthManager();

        if(!$mgr->identityProviderCredentialsAccepted()) {

            $mgr->requireAuth();

        } else {

            $accessToken = $mgr->requestAccessToken();

            unset($_GET["code"]);

            $sfUserInfo = $mgr->getUserInfo();

            $newWikiUser = $this->getNewWikiUser($sfUserInfo);

            var_dump($newWikiUser, $accessToken, $_SESSION);exit;
		}
    }


    public function getNewWikiUser($userInfo) {

        $firstName = $userInfo["given_name"];
        $lastName = $userInfo["family_name"];
        $email = $userInfo["email"];

        $wikiUser = User::createNew("$firstName $lastName", array());
        $wikiUser->setEmail($email);

        return $wikiUser;
    }

}