<?php
/**
*
* acp_extensions [English]
*
* @package language
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
*
*/
/**
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
	'EXTENSION_INVALID_LIST'	=> 'The "%s" extension is not valid.<br /><p>%s</p>',
	'EXTENSION_NOT_AVAILABLE'	=> 'The selected extension is not available for this board, please verify your phpBB and PHP versions are allowed (see the details page).',

	'DETAILS'				=> 'Details',

	'AVAILABLE'				=> 'Available',
	'ENABLED'				=> 'Enabled',
	'DISABLED'				=> 'Disabled',
	'PURGED'				=> 'Purged',
	'UPLOADED'				=> 'Uploaded',

	'ENABLE'				=> 'Enable',
	'DISABLE'				=> 'Disable',
	'PURGE'					=> 'Purge',

	'ENABLE_EXPLAIN'		=> 'Enabling an extension allows you to use it on your board.',
	'DISABLE_EXPLAIN'		=> 'Disabling an extension retains its files and settings but removes any functionality added by the extension.',
	'PURGE_EXPLAIN'			=> 'Purging an extension clears an extensions data while retaining its files.',
	'DELETE_EXPLAIN'		=> 'Deleting an extension removes all of its files and settings. Log entries will remain, although any language variables added by the extension will not be available.',

	'DISABLE_IN_PROGRESS'	=> 'The extension is currently being disabled, please do not leave this page or refresh until it is completed.',
	'ENABLE_IN_PROGRESS'	=> 'The extension is currently being installed, please do not leave this page or refresh until it is completed.',
	'PURGE_IN_PROGRESS'		=> 'The extension is currently being purged, please do not leave this page or refresh until it is completed.',
	'ENABLE_SUCCESS'		=> 'The extension was enabled successfully',
	'DISABLE_SUCCESS'		=> 'The extension was disabled successfully',
	'PURGE_SUCCESS'			=> 'The extension was purged successfully',

	'ENABLE_FAIL'			=> 'The extension could not be enabled',
	'DISABLE_FAIL'			=> 'The extension could not be disabled',
	'PURGE_FAIL'			=> 'The extension could not be purged',

	'EXTENSION_NAME'		=> 'Extension Name',
	'EXTENSION_ACTIONS'		=> 'Actions',
	'EXTENSION_OPTIONS'		=> 'Options',

	'ENABLE_CONFIRM'		=> 'Are you sure that you wish to enable this extension?',
	'DISABLE_CONFIRM'		=> 'Are you sure that you wish to disable this extension?',
	'PURGE_CONFIRM'			=> 'Are you sure that you wish to purge this extension&#39;s data? This will remove all settings stored for this extension and cannot be undone!',

	'WARNING'				=> 'Warning',
	'RETURN'				=> 'Return',

	'EXT_DETAILS'			=> 'Extension Details',
	'DISPLAY_NAME'			=> 'Display Name',
	'CLEAN_NAME'			=> 'Clean Name',
	'TYPE'					=> 'Type',
	'DESCRIPTION'			=> 'Description',
	'VERSION'				=> 'Version',
	'HOMEPAGE'				=> 'Homepage',
	'PATH'					=> 'File Path',
	'TIME'					=> 'Release Time',
	'LICENCE'				=> 'Licence',

	'REQUIREMENTS'			=> 'Requirements',
	'PHPBB_VERSION'			=> 'phpBB Version',
	'PHP_VERSION'			=> 'PHP Version',
	'AUTHOR_INFORMATION'	=> 'Author Information',
	'AUTHOR_NAME'			=> 'Name',
	'AUTHOR_EMAIL'			=> 'Email',
	'AUTHOR_HOMEPAGE'		=> 'Homepage',
	'AUTHOR_ROLE'			=> 'Role',
));
