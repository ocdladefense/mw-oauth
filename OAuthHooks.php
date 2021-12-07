 <?php 

class OAuthHooks {

    private static $loginUrl = "Special:OAuthEndpoint/login";

    private static $logoutUrl = "Special:UserLogout";


    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {

        if(!self::isOauthEndpoint($title)) {

            if(session_id() == '') wfSetupSession();

            // Don't set up the redirect if the user has logged out.
            // Empty redirect sends user to the main page.
            if(!self::isUserLogout($title)) {
                
                $_SESSION["redirect"] = $title->mPrefixedText;
            }
        }

	    return true;
    }


    public static function isOauthEndpoint($title) {

        return strpos($title, "OAuthEndpoint") != false;

    }


    public static function isUserLogout($title) {

        return $title->mPrefixedText == self::$logoutUrl;

    }


    public static function hasAccess($user) {

        return self::isLoggedIn($user);
    }

    
    public static function isLoggedIn($user) {

        return $user->getId() != 0;
    }


    public static function onPersonalUrls( array &$personal_urls, \Title $title ) {

        global $wgScriptPath, $wgUser;

        if(self::isLoggedIn($wgUser)){

           unset($personal_urls["login"]);

        } else {
            
            $personal_urls["login"]["text"] = "OCDLA login";
            $personal_urls["login"]["href"] = "$wgScriptPath/index.php/" . self::$loginUrl;
            $personal_urls["login"]["active"] = true;

            unset($personal_urls["anonuserpage"]);
            unset($personal_urls["anontalk"]);
            unset($personal_urls["anonlogin"]);
        }
	
	    return true;
    }
}