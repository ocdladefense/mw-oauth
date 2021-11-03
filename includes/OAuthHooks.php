<?php 

class OAuthHooks{

    private static $protected = array("Protected", "Test");

    private static $oauthEndpoint = "Special:OAuthEndpoint";

    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {

        $redirectUrl;

        if(self::isPublic($title)) return;
        
        if(!self::hasAccess($title, $user)){

            $request->getSession()->persist();
            $request->setSessionData("redirect", $title->mUrlform);

            $redirectUrl = self::getOAuthEndpoint();

            header("Location: $redirectUrl");

            exit;
        }
    }

    public static function isLogOut($title) {

        return $title->mUrlform == self::$userLogout;
    }


    public static function getOAuthEndpoint(){

        global $wgScriptPath;

        return "$wgScriptPath/index.php/" . self::$oauthEndpoint;
    }

    public static function getLogoutRedirect(){

        global $wgScriptPath;

        return "$wgScriptPath/index.php/" . self::$logoutRedirect;
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


    public static function onPersonalUrls( array &$personal_urls, \Title $title, \SkinTemplate $skin ) {

        global $wgScriptPath, $wgRequest;

        $user = $wgRequest->getSession()->getUser();

        if(self::isLoggedIn($user)){

            unset($personal_urls["login"]);

        } else {
            
            $personal_urls["login"]["text"] = "OCDLA login";
            $personal_urls["login"]["href"] = "$wgScriptPath/index.php/Special:OAuthEndpoint";
            $personal_urls["login"]["active"] = true;

        }

		return true;
	}

    public static function onBeforePageDisplay(\OutputPage $out, \Skin $skin) {

        $userLogout = "UserLogout";

        $logoutRedirect = "Main_Page";


        if(self::isLogOut($out->getTitle())) {

            $redirectUrl = self::getLogoutRedirect();

        }

    }
}