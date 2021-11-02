<?php 

class OAuthHooks{

    public static $protected = array("Protected", "Test");

    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {

        global $wgScriptPath;

        $route = $output->getTitle()->mUrlform;

        if(self::isProtected($route) && !self::isLoggedIn($user)){

            $request->getSession()->persist();
            $request->setSessionData("redirect", $route);

            $url = "$wgScriptPath/index.php/Special:OAuthEndpoint";
            header("Location: $url");

            exit;

        } else if($route == "UserLogout") {

            $url = "$wgScriptPath/index.php/Main_Page";
            header("Location: $url");

        } else {

            return;
        }
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


    public static function isProtected($route) {

        return in_array($route, self::$protected);
    }

    
    public static function isLoggedIn($user) {

        return $user->getId() != 0;

    }
}