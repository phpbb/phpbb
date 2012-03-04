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
	'DISABLE_EXPLAIN'		=> 'Disabling an extension keeps the files and data intact but is not working on your board.',
	'PURGE_EXPLAIN'			=> 'Purging an extension keeps the files but not the data on your board.',
	'DELETE_EXPLAIN'		=> 'Deleting an extension removes all traces of the extension except from your logs.',

	'ENABLE_SUCESS'			=> 'Your extension was enabled sucessfully',
	'DISABLE_SUCESS'		=> 'Your extension was disabled sucessfully',
	'PURGE_SUCESS'			=> 'Your extension was purged sucessfully',
	'DELETE_SUCESS'			=> 'Your extension was deleted sucessfully',

	'ENABLE_FAIL'			=> 'Your extension could not be enabled',
	'DISABLE_FAIL'			=> 'Your extension could not be disabled',
	'PURGE_FAIL'			=> 'Your extension could not be purged',
	'DELETE_FAIL'			=> 'Your extension could not be deleted',

	'EXTENSION_NAME'			=> 'Extension Name',
	'EXTENSION_ACTIONS'			=> 'Actions',
	'EXTENSION_OPTIONS'			=> 'Options',

	'ENABLE_CONFIRM'			=> 'Are you sure you wish to enable this extension?',
	'DISABLE_CONFIRM'			=> 'Are your sure you wish to disable this extension?',
	'PURGE_CONFIRM'				=> 'Are you sure you wish to purge (and not disable) this extension? This action will wipe the extension data on this board and is not reversible.',
	'DELETE_CONFIRM'			=> 'Are you sure you wish to delete (and not purge or disable) this extension? This action will remove the extension and all of its data from this board and is not reversible.',

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

?>
