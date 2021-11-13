 <?php 


class OAuthHooks {

    private static $protected = array();

    private static $loginUrl = "Special:OAuthEndpoint/login";


    // https://lodtest.ocdla.org/index.php/Special:OAuthEndpoint/login
    public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki ) {

        if(self::isWhitelisted($title) || self::isPublic($title)) return true;

        if(!self::hasAccess($user)){
            
	    $_SESSION["redirect"] = $title->mUrlform;

	    session_write_close();

            header("Location: " . self::getLoginUrl());

            exit;
        }

	return true;
    }


    public static function getLoginUrl(){

        return "/index.php/" . self::$loginUrl;
    }


    public static function isPublic($title) {

        return !in_array($title->mUrlform, self::$protected);
    }



    public static function isWhitelisted($title) {

        global $wgWhitelistRead, $wgExtraNamespaces;

	$standardNamespaces = array(0 => null, -1 => "Special");

	$allNamespaces = $standardNamespaces + $wgExtraNamespaces;

        $namespace = $allNamespaces[$title->mNamespace];
    
        $pageName = !empty($namespace) ? "$namespace:" . $title->mTextform : $title->mTextform;
    
        return in_array($pageName, $wgWhitelistRead);
    }


    public static function hasAccess($user) {

        return self::isLoggedIn($user);
    }

    
    public static function isLoggedIn($user) {

        return $user->getId() != 0;
    }


    public static function onPersonalUrls( array &$personal_urls, \Title $title ) {

		//var_dump($_SESSION);exit;


        global $wgScriptPath, $wgUser;

        if(self::isLoggedIn($wgUser)){

           unset($personal_urls["login"]);

        } else {
            
            $personal_urls["login"]["text"] = "OCDLA login";
            $personal_urls["login"]["href"] = "$wgScriptPath/index.php/" . self::$loginUrl;
            $personal_urls["login"]["active"] = true;

        }

		return true;
    }

    public static function onBeforePageDisplay(\OutputPage $out, \Skin $skin) {}
}