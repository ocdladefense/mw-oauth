
# wiki-extension-oauth

### OAuth and Login Related Links.
1. https://www.mediawiki.org/wiki/Extension:OAuth2_Client
2. https://www.mediawiki.org/wiki/Extension:OAuth
3. https://doc.wikimedia.org/mediawiki-core/master/php/classWebRequest.html#a2b571214e80e3998627ecb89cc0d9f56 : Documentation for "WebRequest".
4. https://www.mediawiki.org/wiki/Manual:SessionManager_and_AuthManager
5. https://doc.wikimedia.org/mediawiki-core/1.23.7/php/classOutputPage.html : Documentation for OutputPage class.
6. https://www.mediawiki.org/wiki/Extension:PluggableAuths (Uses the "onBeforeInitialization", ) : pluggableAuth extension (oauth example).
7. https://www.mediawiki.org/wiki/Manual:Database_access
8. https://www.mediawiki.org/wiki/Manual:Hooks : All available mediawiki hooks.
9. https://www.mediawiki.org/wiki/Manual:Hooks/BeforeInitialize : Documentation for the "BeforeInitialize" hook.


### MediaWiki defined database constants
DB_REPLICA = -1
DB_PRIMARY = -2
DB_MASTER = DB_PRIMARY




#### Features ???
This automates the recommended code checkers for PHP and JavaScript code in Wikimedia projects
(see https://www.mediawiki.org/wiki/Continuous_integration/Entry_points).
To take advantage of this automation.

1. install nodejs, npm, and PHP composer
2. change to the extension's directory
3. `npm install`
4. `composer install`

Once set up, running `npm test` and `composer test` will run automated code checks.
 