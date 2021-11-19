<?php

namespace AccessBooksOnline;

use \Salesforce\RestApiRequest;

require __DIR__ . '/ForceApiSubscriptionQuery.php';

use Clickpdx\Salesforce\ForceApiSubscriptionQuery;
 
define('USER_HAS_ACCESS',true);
define('USER_NO_ACCESS',false);

class Access {

	public static function onUserGetRights2(\User $user, array &$aRights) {

		var_dump($aRights);
		return true;
	}

	public static function onUserGetRights(\User $user, array &$aRights) {

		if(!self::isBooksOnlineNamespace()) return true;

		// Guest users should not be able to view online products.  If read is set to true for guest users, you cant override that.  $wgGroupPermissons might take presidence.
		if($user->isAnon()){

			$aRights = array_filter($aRights, function($right){
			
				return $right != "read";
			});
			
			//var_dump($aRights);exit;
			return true;
		}
		
		// Otherwise, check to see if the user has purchased the Books Online product.
		if(self::hasAccess()){
			
			$aRights[] = "read";

		} else {

			$aRights = array_filter($aRights, function($right){
			
				return $right != "read";
			});
		}

		return true;
	}



	public static function hasAccess() {

		//$contactId = $_SESSION["sf-contact-id"];

		// Id of a contact with a DUII orderItem
		$contactId = "003j000000rU8rmAAC";

		$productIds = self::getNamespaceProductIds();

		return self::didPurchaseProducts($contactId, $productIds);
	}



	public static function didPurchaseProducts($contactId, $productIds) {

		$orderItemIds = self::getUsersOrderItems($contactId, $productIds);

		return !empty($orderItemIds);
	}



	public static function isBooksOnlineNamespace() {

		global $wgOcdlaBooksOnlineNamespaces, $wgTitle;

		return in_array($wgTitle->mNamespace, $wgOcdlaBooksOnlineNamespaces);
	}



	private static function getUsersOrderItems($contactId, $productIds) {

		$accessToken = $_SESSION["access-token"];
		$instanceUrl = $_SESSION["instance-url"];
		
		$api = new RestApiRequest($instanceUrl, $accessToken);

		$soqlProdIds = "'" . implode("','", $productIds) . "'";

		$query = "SELECT Id FROM OrderItem WHERE Contact__c = '$contactId' AND Product2Id IN($soqlProdIds)";

		$resp = $api->query($query);

		if(!$resp->success()) throw new \Exception($resp->getErrorMessage());

		$orderItemIds = array();
		foreach($resp->getRecords() as $record) {

			$orderItemIds[] = $record["Id"];
		}

		return $orderItemIds;
	}

	private static function getNamespaceProductIds(){

		global $wgTitle, $wgExtraNamespaces;

		$namespace = $wgExtraNamespaces[$wgTitle->mNamespace];

		$accessToken = $_SESSION["access-token"];
		$instanceUrl = $_SESSION["instance-url"];
		
		$api = new RestApiRequest($instanceUrl, $accessToken);

		$query = "SELECT Id FROM Product2 WHERE Name LIKE '%$namespace%'";

		$resp = $api->query($query);

		if(!$resp->success()) throw new \Exception($resp->getErrorMessage());

		$records = $resp->getRecords();

		$productIds = array();
		foreach($records as $record){

			$productIds[] = $record["Id"];
		}

		return $productIds;
	}







	# By default, we restrict access to reading articles by setting "read" to "false", however, allow access for the bot.
	public static function init(){

		global $wgGroupPermissions, $wgAutoloadClasses;
		
		$wgGroupPermissions['*']['read'] = false;	
		
		if(strpos($_SERVER['HTTP_USER_AGENT'],'Appserver') !== false) $wgGroupPermissions['*']['read'] = true;
		
		$wgAutoloadClasses['SalesforceGrantManager'] = 'extensions/WhitelistedNamespaces/classes/SalesforceGrantManager.php';
	}


	# Adds currently viewed page to $wgWhitelistRead if page is in whitelisted namespace, or the user has special access to the page.
	public static function hasAccess2($user, $rights){

		self::init();

		if(strpos($_SERVER['HTTP_USER_AGENT'], 'Appserver') !== false) return USER_HAS_ACCESS;

		# Globals not being used
		global $wgOcdlaSessionDBtype, $wgWebStoreDomain;

		# Custom globals
		global $wgOcdlaSessionDBserver, $wgOcdlaSessionDBname, $wgOcdlaSessionDBuser, $wgOcdlaSessionDBpassword, $wgWhitelistedNamespaces;
		global $wgSsoSession, $wgAuthOcdla_LoginURL, $wgWhitelistedNamespacesFreeTrialQuery, $wgOcdlaBonStoreLink;

		# Framework globals
		global $wgTitle, $wgServer;
		

		$retURL = urlencode( $wgServer . '/'.$wgTitle);

		$LoginPage = $wgAuthOcdla_LoginURL .'?retURL='.$retURL;

		$action = isset($_GET['action']) ? $_GET['action'] : 'view';
		
		$namespace = $wgTitle->getNamespace();
		
		$nstext = $wgTitle->getNsText();
		
		$title = $wgTitle->getFullText();

		$login = "<a href='{$LoginPage}'>Login</a>";

		
		if($namespace == NS_LEGCOMM && !self::userHasPermission("read-legcomm", $rights)) {

			# The user must have legcomm rights to view legcomm pages.
			self::set401Headers();
			die("You don't have the appropriate permissions to access this document.  {$login} to view it.");

		} else if(self::isBooksOnlineNamespace($namespace) && $user->getId() === 0 && $action != 'edit') {

			#Anonymous users can't view Books Online materials.
			self::set401Headers();
			die("Guest users cannot view OCDLA Books Online publications.  {$login} to view it.");

		} else if(self::isBooksOnlineNamespace($namespace) && !self::userHasPermission("read-subscriptions", $rights)) {

			$dbCreds = array(
				'host' 	   => $wgOcdlaSessionDBserver,
				'user' 	   => $wgOcdlaSessionDBuser,
				'password' => $wgOcdlaSessionDBpassword,
				'dbname'   => $wgOcdlaSessionDBname,
				'dbName'   => $wgOcdlaSessionDBname
			);
		
			try {

				$wgSsoSession = \DatabaseBase::factory( 'ocdlasession', $dbCreds);

				$productId;  // The product 2 id
				
				$contactId = $wgSsoSession->getContactId(); // Test Customer Id	"0018D000002s6kDQAQ";
			
				$api = new ForceApiSubscriptionQuery();

				if(self::userHasOnlineSubscriptionAccess($api, $contactId)) {
					
					return USER_HAS_ACCESS;
				}
				
				
				$grantManager = new SalesforceGrantManager($api);
				$grantManager->setSalesforceQuery($wgWhitelistedNamespacesFreeTrialQuery);
				$grantManager->setContactId($contactId);
				$grantManager->doApi();
				
				# Free Trial access
				if($grantManager->hasNamespaceAccess($namespace)) {
					
					$grantInfo = $grantManager->getGrantInfo($namespace);
					$productName = $grantInfo['PricebookEntry.Product2.Name'];
					$expiryDate = $grantInfo['ExpirationDate__c'];
					
					$user->ocdlaMessages[] = "We hope you're enjoying your {$productName}.  Your subscription will expire on {$expiryDate}.<br />Renew for a full year of <a href='{$wgOcdlaBonStoreLink}'>Books Online manuals here</a>.";
					
					if($namespace == NS_SSM) {

						$user->ocdlaMessages = array("<span class='message-heading'>upgrade now!</span>OCDLA's Search & Seizure in Oregon was revised September, 2018.  The updated manual contains 90 additional pages of developments in search and seizure case law with analysis. For complete access to the latest Search & Seizure updates, <a href='{$wgOcdlaBonStoreLink}' target='_new'>become a full subscriber</a>.");
					}
					
					return USER_HAS_ACCESS;

				} else {

					self::set401Headers();

					if($user->isAnon()) {

						die("You don't have the appropriate permissions to access this document.  {$login} to view it.");

					} else {

						die("To view this document please purchase the <a href='".$wgOcdlaBonStoreLink."'>OCDLA Online Books subscription</a>.");
					}
				}

			} catch(\Exception $e) {

				self::set401Headers();
				die("There was an error determining your access to this document: {$e->getMessage()}.");
			}

		} else if(self::isBooksOnlineNamespace($namespace) && $action == 'edit' && !in_array("edit-subscriptions", $right)) {

			self::set401Headers();
			die("You don't have the appropriate permissions to access this document.  {$login} to view it.");

		} else if(false && $namespace < NS_BLOG && $user->isAnon()) { // Page is not whitelisted and user doesn't have a valid session.

			header("Location: {$LoginPage}", true, 301);
			exit;
		}

		// Otherwise, public users can view this page
		return USER_HAS_ACCESS; 
	}





	public static function userHasPermission($permission, $userRights) {

		return in_array($permission, $userRights);
	}


	public static function userHasOnlineSubscriptionAccess($api, $contactId) {

		global $wgWhitelistedNamespacesOrderLineQuery, $wgWhitelistedNamespacesSubscriptionQuery;

		return $api->hasOnlineSubscriptionAccess($wgWhitelistedNamespacesOrderLineQuery,$contactId) || $api->hasOnlineSubscriptionAccess($wgWhitelistedNamespacesSubscriptionQuery,$contactId);
	}



	public static function set401Headers() {

		header("HTTP/1.1 401 Unauthorized" );
		header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
		header("Cache-Control: no-cache, max-age=0, must-revalidate, no-store");	
	}

}