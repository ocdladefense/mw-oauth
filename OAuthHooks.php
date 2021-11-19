 <?php 

//use AccessWhitelistNamespace\Access as WhitelistNamespace;
//use AccessBooksOnline\Access as BooksOnline;


class OAuthHooks {

    private static $loginUrl = "Special:OAuthEndpoint/login";

    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {

/**
	global $autoLogin;

        if(self::isWhitelisted($title)) return true;

        if(!self::hasAccess($user)){

	    // Need this to access the session
	    if(session_id() == '') wfSetupSession();

	    $_SESSION["redirect"] = $title->mUrlform;

            header("Location: " . self::getLoginUrl());

            exit;
        }
*/

	    return true;
    }



	public static function onUserGetRights( User $user, array &$aRights ) {

        // global $wgWhitelistRead, $wgRequest;

        // $title = $wgR

        // if($in)

        if(WhitelistNamespace::hasAccess()) $aRights[] = "read";

        //if(BooksOnline::hasAccess($user, $aRights)) $aRights[] = "read";
        
        //var_dump($aRights);exit;
	    return true;
    }


    public static function getLoginUrl(){

        return "/index.php/" . self::$loginUrl;
    }


    public static function isWhitelisted($title) {

        global $wgWhitelistRead, $wgExtraNamespaces, $wgWhitelistedNamespaces;

        $standardNamespaces = array(0 => null, -1 => "Special");

        $allNamespaces = $standardNamespaces + $wgExtraNamespaces;

        $namespace = $allNamespaces[$title->mNamespace];

        $pageName = !empty($namespace) ? "$namespace:" . $title->mTextform : $title->mTextform;

        // var_dump($pageName);exit;

        if(self::isWhitelistedNamespace($namespace)){

            $wgWhitelistRead[] = $pageName;
        }
    
        return in_array($pageName, $wgWhitelistRead);
    }


    public static function isWhitelistedNamespace($namespace) {

        global $wgWhitelistedNamespaces, $wgExtraNamespaces;

        $namespaceInt = array_search($namespace, $wgExtraNamespaces);
    
        return in_array($namespaceInt, $wgWhitelistedNamespaces);
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

    public static function onBeforePageDisplay(\OutputPage $out, \Skin $skin) {return true;}
}