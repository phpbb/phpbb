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
	'EXTENSION'					=> 'Extension',
	'EXTENSIONS'				=> 'Extensions',
	'EXTENSIONS_ADMIN'			=> 'Extensions Manager',
	'EXTENSIONS_EXPLAIN'		=> 'The Extensions Manager is a tool in your phpBB Board which allows you to manage all of your extensions statuses and view information about them.',
	'EXTENSION_INVALID_LIST'	=> 'The “%s” extension is not valid.<br />%s<br /><br />',
	'EXTENSION_NOT_AVAILABLE'	=> 'The selected extension is not available for this board, please verify your phpBB and PHP versions are allowed (see the details page).',
	'EXTENSION_DIR_INVALID'		=> 'The selected extension has an invalid directory structure and cannot be enabled.',
	'EXTENSION_NOT_ENABLEABLE'	=> 'The selected extension cannot be enabled, please verify the extension’s requirements.',
	'EXTENSION_NOT_INSTALLED'	=> 'The extension %s is not available. Please check that you have installed it correctly.',

	'DETAILS'				=> 'Details',

	'EXTENSIONS_NOT_INSTALLED'	=> 'Not installed Extensions',
	'EXTENSIONS_DISABLED'		=> 'Disabled Extensions',
	'EXTENSIONS_ENABLED'		=> 'Enabled Extensions',

	'EXTENSION_DELETE_DATA'	=> 'Delete data',
	'EXTENSION_DISABLE'		=> 'Disable',
	'EXTENSION_ENABLE'		=> 'Enable',

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
));
