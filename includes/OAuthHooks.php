<?php 

class OAuthHooks{

    public static $protected = array("Baz", "Foo");

    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {

        global $wgScriptPath;

        // Check to see if the route is protected 
        $route = $title->mTextform;

        if($route == "Main Page"){

            var_dump($user, $request);exit;

        }

        if(self::isProtected($route)){

            $url = "$wgScriptPath/index.php/Special:OAuthEndpoint";

            header("Location: $url");

            exit;

        } else {

            return;
        }
    }

    public static function onPersonalUrls( array &$personal_urls, \Title $title, \SkinTemplate $skin ) {

        global $wgScriptPath;

		$personal_urls["login"]["text"] = "OCDLA login";
		$personal_urls["login"]["href"] = "$wgScriptPath/index.php/Special:OAuthEndpoint";
		$personal_urls["login"]["active"] = true;

		return true;
	}


    public static function isProtected($route) {

        return in_array($route, self::$protected);
    }

    
    public static function userIsLoggedIn($userId) {

        return $userId != 0;

    }
}