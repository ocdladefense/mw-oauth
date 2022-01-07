<?php 



class OAuthHooks {

    // Determine if an incoming request is for a
    // login-related endpoint.
    private static $loginTitle = "OAuthEndpoint";


    // Used in Personal URLs.
    private static $loginUrl = "Special:OAuthEndpoint/login";


    // Determine if an incoming request is for a
    // logout-related endpoint.
    private static $logoutTitle = "UserLogout";


    // Temporary work around.  Avoid executing our initialization function for 
    // real files.  For example, don't set the redirect to a JavaScript or CSS file.
    private static $fileExtensionChar = ".";
    



    /**
     * What is the goal of this function?
     * // NOTE: $title object contains the equivalent
     * of getters and setters so accessing some properties
     *  will result in other properties being loaded from the database.
     */
    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {

        // global $wgExtraNamespaces;
        
        // $namespaceName = $wgExtraNamespaces[$title->mNamespace];


        // Don't save the redirect to the session if the referer is the logout page.
        // An empty redirect sends the user to the main page.
        if(!self::isLoginLogoutPage($title) && self::isValidRedirect($title)) {

            if(session_id() == '') wfSetupSession();
            $_SESSION["redirect"] = $title->getPrefixedUrl();
        }
         
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

        // Assume this means it does not have a file extension.
        return count($parts) === 1; 
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