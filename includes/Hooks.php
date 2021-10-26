<?php


namespace MediaWiki\Extension\OAuth;


require(__DIR__ . "/../config/config.php");


class Hooks implements \MediaWiki\Hook\BeforePageDisplayHook {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * @param \OutputPage $out
	 * @param \Skin $skin
	 */

	public function onBeforePageDisplay($out, $skin): void {

		$shouldDoLogin = $out->getPageTitle() == "Log in";

		$mgr = new OAuthManager();

		if($shouldDoLogin  && !$mgr->identityProviderCredentialsAccepted()) {

			$mgr->requireAuth();
		}

		if($mgr->identityProviderCredentialsAccepted()) {

			$credentials = $mgr->getAccessToken();

			var_dump($credentials, $_SESSION, $_GET);exit;
		}
	}
}
