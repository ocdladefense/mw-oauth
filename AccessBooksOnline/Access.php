<?php

namespace AccessBooksOnline;

use \Salesforce\RestApiRequest;



class Access {

	public static function onUserGetRights(\User $user, array &$aRights) {

		if(!self::isBooksOnlineNamespace()) return true;

		// Guest users should not be able to view online products.  If read is set to true for guest users, you cant override that.  $wgGroupPermissons might take presidence.
		if($user->isAnon()){

			$aRights = array_filter($aRights, function($right){
			
				return $right != "read";
			});
			
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

		$contactId = $_SESSION["sf-contact-id"];

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
}