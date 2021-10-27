<?php

use MediaWiki\Extension\OAuth\OAuthManager;

require("config/config.php");

class SpecialOAuthEndpoint extends SpecialPage {

    public function __construct() {

        parent::__construct("OAuthEndpoint");
    }


    public function execute($parameter) {

        $mgr = new OAuthManager();

        if(!$mgr->identityProviderCredentialsAccepted()) {

            $mgr->requireAuth();

        } else {

            $credentials = $mgr->getAccessToken();

            unset($_GET["code"]);

            var_dump($credentials, $_SESSION, $_GET);exit;
		}
    }

}