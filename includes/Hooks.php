<?php


namespace MediaWiki\Extension\OAuth;

use \Salesforce\OAuthConfig;
use \Salesforce\OAuth;

class Hooks implements \MediaWiki\Hook\BeforePageDisplayHook {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * @param \OutputPage $out
	 * @param \Skin $skin
	 */
	public function onBeforePageDisplay($outputPage, $skin): void {

		$shouldDoLogin = $outputPage->getPageTitle() == "Log in";

		if($shouldDoLogin) {

			var_dump($outputPage);exit;

			// $out->addHTML( \Html::element( 'p', [], 'THIS IS ONLY SHOWING ON THE LOGIN PAGE!' ) );
			// $out->addModules( 'oojs-ui-core' );
		}
	}

}
