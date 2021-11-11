<?php 


class OAuthHooks {


    private static $protected = array("Protected", "Test");

    private static $loginUrl = "Special:OAuthEndpoint/login";



    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {

        if(self::isPublic($title)) return true;
        
        if(!self::hasAccess($title, $user)){
            
            //$request->getSession()->persist();
            //$request->setSessionData("redirect", $title->mUrlform);
	    $_SESSION["redirect"] = $title->mUrlform;

            header("Location: " . self::getLoginUrl());

            return true;
        }
    }


    public static function getLoginUrl(){

        return "/index.php/" . self::$loginUrl;
    }

    public static function isProtected($title) {

        return in_array($title->mUrlform, self::$protected);
    }


    public static function isPublic($title) {

        return !in_array($title->mUrlform, self::$protected);
    }


    public static function hasAccess($title, $user) {

        return self::isLoggedIn($user);
    }

    
    public static function isLoggedIn($user) {

        return $user->getId() != 0;
    }


    public static function onPersonalUrls( array &$personal_urls, \Title $title ) {

        //global $wgScriptPath, $wgRequest;

        //$user = $wgRequest->getSession()->getUser();

        //if(self::isLoggedIn($user)){

           // unset($personal_urls["login"]);

        //} else {
            
            $personal_urls["login"]["text"] = "OCDLA login";
            $personal_urls["login"]["href"] = "$wgScriptPath/index.php/" . self::$loginUrl;
            $personal_urls["login"]["active"] = true;

        //}

		return true;
	}

    public static function onBeforePageDisplay(\OutputPage $out, \Skin $skin) {}
}