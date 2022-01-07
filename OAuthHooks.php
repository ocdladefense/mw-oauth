<?php 

class OAuthHooks {

    private static $loginTitle = "OAuthEndpoint";

    private static $loginUrl = "Special:OAuthEndpoint/login";

    private static $logoutTitle = "UserLogout";

    private static $fileExtensionChar = ".";
    
    /**
     * What is the goal of this function?
     * // NOTE: $title object contains the equivalent
     * of getters and setters so accessing some properties
     *  will result in other properties being loaded from the database.
     */
    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {


        // Don't save the redirect to the session if the referer is the logout page.
        // An empty redirect sends the user to the main page.
        if(!self::isLoginLogoutPage($title) && self::isValidRedirect($title)) {

<<<<<<< HEAD
            // Don't save the redirect to the session if the referer is the logout page.
            // An empty redirect sends the user to the main page.
            if(!self::isUserLogout($title)) {
                
                $_SESSION["redirect"] = $title->getPrefixedUrl();
            }
        }
=======
            if(session_id() == '') wfSetupSession();
            $_SESSION["redirect"] = $title->mUrlform;
        }   
>>>>>>> ee1561552a4af040afad24096eb490b99db25670

	    return true;  
    }


    public static function isLoginLogoutPage($title) {

        return self::isUserLogin($title) || self::isUserLogout($title);
    }


    public static function isUserLogin($title) {

        return strpos($title->mUrlform, self::$loginTitle) !== false;
    }


    public static function isUserLogout($title) {

        return $title->mUrlform == self::$logoutTitle;
    }


    public static function isValidRedirect($title) {

        $parts = explode(self::$fileExtensionChar, $title->mUrlform);

        return count($parts) === 1; // Assume this means it has a file extension.
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
            $personal_urls["login"]["href"] = "$wgScriptPath/" . self::$loginUrl;
            $personal_urls["login"]["active"] = true;

            unset($personal_urls["anonuserpage"]);
            unset($personal_urls["anontalk"]);
            unset($personal_urls["anonlogin"]);
        }
	
	    return true;
    }
}