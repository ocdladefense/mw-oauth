<?php


namespace MediaWiki\Extension\OAuth;


class HookAddLink {


	public static function onPersonalUrls( array &$personal_urls, \Title $title, \SkinTemplate $skin ) {

		$personal_urls["login"]["text"] = "OCDLA login";
		$personal_urls["login"]["href"] = "/mwiki/index.php/Special:OAuthEndpoint";
		$personal_urls["login"]["active"] = true;

		return true;
	}
}
