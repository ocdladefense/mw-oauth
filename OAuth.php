<?php


// This is a test comment.
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/OAuth/OAuth.php" );
EOT;
	exit( 1 );
}


 
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'OAuth',
	'author' => 'Trevor Uehlin',
	'url' => 'https://github.com/ocdladefense/wiki-extension-oauth.git',
	'descriptionmsg' => 'oauth-desc',
	'version' => '2.0.2',
);

// White list the special page, so it is public.
$wgWhitelistRead[] = "Special:OAuthEndpoint";
 

# Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgAutoloadClasses['SpecialOAuthEndpoint'] = __DIR__ . '/SpecialOAuthEndpoint.php';
$wgAutoloadClasses['OAuthHooks'] = __DIR__ . '/includes/OAuthHooks.php';

$wgHooks['PersonalUrls'][] = 'OAuthHooks::onPersonalUrls';
$wgHooks['BeforeInitialize'][] = 'OAuthHooks::onBeforeInitialize';



# Tell MediaWiki about the new special page and its class name
$wgSpecialPages['OAuthEndpoint'] = 'SpecialOAuthEndpoint';