<?php
/** 
*
* acp_mods [English]
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
	'EXTENSION'				=> 'Extension',
	'EXTENSIONS'			=> 'Extensions',
	'EXTENSIONS_ADMIN'		=> 'Extensions Admin',
	'EXTENSIONS_EXPLAIN'		=> 'The Extensions Admin is a tool in your phpBB Board which allows you to manage all of your extensions. For more information about extensions please visit <a href="http://phpbb.com/mods/extensions/">this page</a> on phpBBs Offical Website.',

	'DETAILS'				=> 'Details',

	'AVALIABLE'				=> 'Avaliable',
	'ENABLED'				=> 'Enabled',
	'DISBALED'				=> 'Disabled',

	'ENABLE'				=> 'Enable',
	'DISABLE'				=> 'Disable',
	'PURGE'					=> 'Purge',
	'DELETE'				=> 'Delete',

	'ENABLED'				=> 'Enabled',
	'DISABLED'				=> 'Disabled',
	'PURGED'				=> 'Purged',
	'DELETED'				=> 'Deleted',

	'ENABLE_EXPLAIN'		=> 'Enabling an extension allows you to use it on your board.',
	'DISABLE_EXPLAIN'		=> 'Disabling an extension retains its files and settings but removes any functionality added by the extension.',
	'PURGE_EXPLAIN'			=> 'Purging an extension clears an extensions data while retaining its files.',
	'DELETE_EXPLAIN'		=> 'Deleting an extension removes all of its files and settings. Log entries will remain, although any language variables added by the extension will not be available.',

	'ENABLE_SUCESS'			=> 'The extension was enabled sucessfully',
	'DISABLE_SUCESS'		=> 'The extension was disabled sucessfully',
	'PURGE_SUCESS'			=> 'The extension was purged sucessfully',
	'DELETE_SUCESS'			=> 'The extension was deleted sucessfully',

	'ENABLE_FAIL'			=> 'The extension could not be enabled',
	'DISABLE_FAIL'			=> 'The extension could not be disabled',
	'PURGE_FAIL'			=> 'The extension could not be purged',
	'DELETE_FAIL'			=> 'The extension could not be deleted',

	'EXTENSION_NAME'			=> 'Extension Name',
	'EXTENSION_ACTIONS'			=> 'Actions',
	'EXTENSION_OPTIONS'			=> 'Options',

	'ENABLE_CONFIRM'			=> 'Are you sure you wish to enable this extension?',
	'DISABLE_CONFIRM'			=> 'Are your sure you wish to disable this extension?',
	'PURGE_CONFIRM'				=> 'Are you sure you wish to purge this extensions data? This cannot be undone.',
	'DELETE_CONFIRM'			=> 'Are you sure you wish to data this extensions files and clear its data? This cannot be undone.',

	'WARNING'			=> 'Warning',
	'RETURN'			=> 'Return',

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
	'PHP_VERSION'			=> 'php Version',
	'AUTHOR_INFORMATION'	=> 'Author Information',
	'AUTHOR_NAME'			=> 'Author Name',
	'AUTHOR_USERNAME'		=> 'Author Username',
	'AUTHOR_EMAIL'			=> 'Author Email',
	'AUTHOR_HOMEPAGE'		=> 'Author Homepage',
	'AUTHOR_ROLE'			=> 'Author Role',
));
