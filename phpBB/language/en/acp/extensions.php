<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(

	'EXTENSION_ALREADY_INSTALLED'				=> 'The “%s” extension has already been installed.',
	'EXTENSION_ALREADY_INSTALLED_MANUALLY'		=> 'The “%s” extension has already been installed manually.',
	'EXTENSION_ALREADY_MANAGED'					=> 'The “%s” extension is already managed.',
	'EXTENSION_CANNOT_MANAGE_FILESYSTEM_ERROR'	=> 'The “%s” extension cannot be managed because the existing files could not be removed from the filesystem.',
	'EXTENSION_CANNOT_MANAGE_INSTALL_ERROR'		=> 'The “%s” extension could not be installed. The prior installation of this extension has been restored.',
	'EXTENSION_MANAGED_WITH_CLEAN_ERROR'		=> 'The “%1$s” extension has been installed but an error occurred and the old files could not be removed. You might want to delete the “%2$s” files manually.',
	'EXTENSION_MANAGED_WITH_ENABLE_ERROR'		=> 'The “%s” extension has been installed but an error occurred while enabling it.',
	'EXTENSION_NOT_INSTALLED'					=> 'The “%s” extension is not installed.',

	'ENABLING_EXTENSIONS'	=> 'Enabling extensions',
	'DISABLING_EXTENSIONS'	=> 'Disabling extensions',

	'EXTENSIONS_CATALOG'			=> 'Extensions Catalog',
	'EXTENSIONS_CATALOG_EXPLAIN'	=> 'Here you can browse all of the extensions available for your phpBB board. Extensions can easily be installed or removed with just a click. Adjust the settings to allow instant enabling and purging of extensions.',

	'EXTENSION'					=> 'Extension',
	'EXTENSIONS'				=> 'Extensions',
	'EXTENSIONS_ADMIN'			=> 'Extensions Manager',
	'EXTENSIONS_EXPLAIN'		=> 'The Extensions Manager is a tool in your phpBB Board which allows you to manage all of your extensions statuses and view information about them.',
	'EXTENSION_INVALID_LIST'	=> 'The “%s” extension is not valid.<br />%s<br /><br />',
	'EXTENSION_NOT_AVAILABLE'	=> 'The selected extension is not available for this board, please verify your phpBB and PHP versions are allowed (see the details page).',
	'EXTENSION_DIR_INVALID'		=> 'The selected extension has an invalid directory structure and cannot be enabled.',
	'EXTENSION_NOT_ENABLEABLE'	=> 'The selected extension cannot be enabled, please verify the extension’s requirements.',

	'DETAILS'				=> 'Details',

	'EXTENSIONS_DISABLED'	=> 'Disabled Extensions',
	'EXTENSIONS_ENABLED'	=> 'Enabled Extensions',

	'EXTENSION_DELETE_DATA'	=> 'Delete data',
	'EXTENSION_DISABLE'		=> 'Disable',
	'EXTENSION_ENABLE'		=> 'Enable',
	'EXTENSION_UPDATE'		=> 'Update',
	'EXTENSION_REMOVE'		=> 'Remove',

	'EXTENSION_DELETE_DATA_EXPLAIN'	=> 'Deleting an extension’s data removes all of its data and settings. The extension files are retained so it can be enabled again.',
	'EXTENSION_DISABLE_EXPLAIN'		=> 'Disabling an extension retains its files, data and settings but removes any functionality added by the extension.',
	'EXTENSION_ENABLE_EXPLAIN'		=> 'Enabling an extension allows you to use it on your board.',

	'EXTENSION_DELETE_DATA_IN_PROGRESS'	=> 'The extension’s data is currently being deleted. Please do not leave or refresh this page until it is completed.',
	'EXTENSION_DISABLE_IN_PROGRESS'	=> 'The extension is currently being disabled. Please do not leave or refresh this page until it is completed.',
	'EXTENSION_ENABLE_IN_PROGRESS'	=> 'The extension is currently being enabled. Please do not leave or refresh this page until it is completed.',

	'EXTENSION_DELETE_DATA_SUCCESS'	=> 'The extension’s data was deleted successfully',
	'EXTENSION_DISABLE_SUCCESS'		=> 'The extension was disabled successfully',
	'EXTENSION_ENABLE_SUCCESS'		=> 'The extension was enabled successfully',

	'EXTENSION_NAME'			=> 'Extension Name',
	'EXTENSION_ACTIONS'			=> 'Actions',
	'EXTENSION_OPTIONS'			=> 'Options',
	'EXTENSION_INSTALL_HEADLINE'=> 'Installing an extension',
	'EXTENSION_INSTALL_EXPLAIN'	=> '<ol>
			<li>Download an extension from phpBB’s extensions database</li>
			<li>Unzip the extension and upload it to the <samp>ext/</samp> directory of your phpBB board</li>
			<li>Enable the extension, here in the Extensions manager</li>
		</ol>',
	'EXTENSION_UPDATE_HEADLINE'	=> 'Updating an extension',
	'EXTENSION_UPDATE_EXPLAIN'	=> '<ol>
			<li>Disable the extension</li>
			<li>Delete the extension’s files from the filesystem</li>
			<li>Upload the new files</li>
			<li>Enable the extension</li>
		</ol>',
	'EXTENSION_REMOVE_HEADLINE'	=> 'Completely removing an extension from your board',
	'EXTENSION_REMOVE_EXPLAIN'	=> '<ol>
			<li>Disable the extension</li>
			<li>Delete the extension’s data</li>
			<li>Delete the extension’s files from the filesystem</li>
		</ol>',

	'EXTENSION_DELETE_DATA_CONFIRM'	=> 'Are you sure that you wish to delete the data associated with “%s”?<br /><br />This removes all of its data and settings and cannot be undone!',
	'EXTENSION_DISABLE_CONFIRM'		=> 'Are you sure that you wish to disable the “%s” extension?',
	'EXTENSION_ENABLE_CONFIRM'		=> 'Are you sure that you wish to enable the “%s” extension?',
	'EXTENSION_FORCE_UNSTABLE_CONFIRM'	=> 'Are you sure that you wish to force the use of unstable version?',

	'INSTALLED'				=> 'Installed',
	'INSTALLED_MANUALLY'	=> 'Installed manually',

	'RETURN_TO_EXTENSION_LIST'	=> 'Return to the extension list',

	'EXT_DETAILS'			=> 'Extension Details',
	'DISPLAY_NAME'			=> 'Display Name',
	'CLEAN_NAME'			=> 'Clean Name',
	'TYPE'					=> 'Type',
	'DESCRIPTION'			=> 'Description',
	'VERSION'				=> 'Version',
	'HOMEPAGE'				=> 'Homepage',
	'PATH'					=> 'File Path',
	'TIME'					=> 'Release Time',
	'LICENSE'				=> 'Licence',

	'REQUIREMENTS'			=> 'Requirements',
	'PHPBB_VERSION'			=> 'phpBB Version',
	'PHP_VERSION'			=> 'PHP Version',
	'AUTHOR_INFORMATION'	=> 'Author Information',
	'AUTHOR_NAME'			=> 'Name',
	'AUTHOR_EMAIL'			=> 'Email',
	'AUTHOR_HOMEPAGE'		=> 'Homepage',
	'AUTHOR_ROLE'			=> 'Role',

	'NOT_UP_TO_DATE'		=> '%s is not up to date',
	'UP_TO_DATE'			=> '%s is up to date',
	'ANNOUNCEMENT_TOPIC'	=> 'Release Announcement',
	'DOWNLOAD_LATEST'		=> 'Download Version',
	'NO_VERSIONCHECK'		=> 'No version check information given.',

	'VERSIONCHECK_FORCE_UPDATE_ALL'		=> 'Re-Check all versions',
	'FORCE_UNSTABLE'					=> 'Always check for unstable versions',
	'EXTENSIONS_VERSION_CHECK_SETTINGS'	=> 'Version check settings',

	'BROWSE_EXTENSIONS_DATABASE'		=> 'Browse extensions database',

	'META_FIELD_NOT_SET'	=> 'Required meta field %s has not been set.',
	'META_FIELD_INVALID'	=> 'Meta field %s is invalid.',

	'EXTENSIONS_CATALOG_SETTINGS'	=> 'Extensions catalog settings',
	'ENABLE_ON_INSTALL'				=> 'Enable extensions while installing',
	'PURGE_ON_REMOVE'				=> 'Purge extensions while removing',
	'ENABLE_PACKAGIST'				=> 'Search packagist',
	'ENABLE_PACKAGIST_EXPLAIN'		=> 'Search packagist for phpBB extensions. Beware that packagist may contain extensions not validated by the phpBB Extension Customisations Team.',
	'ENABLE_PACKAGIST_CONFIRM'		=> 'Are you sure you want to search packagist?',
	'COMPOSER_REPOSITORIES'			=> 'Repositories',
	'COMPOSER_REPOSITORIES_EXPLAIN'	=> 'Add URLs to Composer repositories of phpBB extensions to search here, one per line (must be the base url of the packages.json file).',
	'NO_EXTENSION_AVAILABLE'		=> 'There are no extension available for your board',

	'EXTENSION_MANAGED_SUCCESS'		=> 'The extension %s is now being managed automatically.',
	'EXTENSIONS_INSTALLED'			=> 'Extensions successfully installed.',
	'EXTENSIONS_REMOVED'			=> 'Extensions successfully removed.',
	'EXTENSIONS_UPDATED'			=> 'Extensions successfully updated.',

	'EXTENSIONS_CATALOG_NOT_AVAILABLE'	=> 'The extensions catalog is not available',
	'EXTENSIONS_COMPOSER_NOT_WRITABLE'	=> 'In order to use the catalog, the following files and directories must be writable: ext/ vendor-ext/ composer-ext.json and composer-ext.lock',

	'STABILITY_STABLE'	=> 'stable',
	'STABILITY_RC'		=> 'RC',
	'STABILITY_BETA'	=> 'beta',
	'STABILITY_ALPHA'	=> 'alpha',
	'STABILITY_DEV'		=> 'dev',

	'COMPOSER_MINIMUM_STABILITY'			=> 'Minimum stability',
	'COMPOSER_MINIMUM_STABILITY_EXPLAIN'	=> 'Always use <samp>stable</samp> versions on a live forum. Non-stable versions may still be in development and could cause unexpected problems with your forum and should only be used for development purposes in local or staging environments.',

));
