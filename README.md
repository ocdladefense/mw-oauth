# wiki-extension-oauth

### Create a new MediaWiki Extension.
1. On GitHub, create a new MediaWiki extension repository, using the "wiki-extension-template" as a template. (https://github.com/ocdladefense/wiki-extension-template.git)
2. Clone the new extension repository into your MediaWiki installation's "extensions" directory and rename it if needed.
4. Update the extensions.json file.  Change all "BoilerPlate" references to your extensions name.
5. Change the hook callback's conditional operator to "true || ..." so that the code executes regardless of whats in the config.
6. Activate the new extension in your MediaWiki installation by adding "wfLoadExtension('ExtensionDirName')" to "LocalSettings.php".
7. Refresh the page and you should see the text that you specified in the callback.


#### Features ???
This automates the recommended code checkers for PHP and JavaScript code in Wikimedia projects
(see https://www.mediawiki.org/wiki/Continuous_integration/Entry_points).
To take advantage of this automation.

1. install nodejs, npm, and PHP composer
2. change to the extension's directory
3. `npm install`
4. `composer install`

Once set up, running `npm test` and `composer test` will run automated code checks.
