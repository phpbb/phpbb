<?php
/**
*
* migrator [English]
*
* @package language
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'CONFIG_NOT_EXIST'					=> 'The config setting "%s" unexpectedly does not exist.',

	'GROUP_NOT_EXIST'					=> 'The group "%s" unexpectedly does not exist.',

	'MIGRATION_DATA_DONE'				=> 'Installed Data: %1$s; Time: %2$.2f seconds',
	'MIGRATION_DATA_IN_PROGRESS'		=> 'Installing Data: %1$s; Time: %2$.2f seconds',
	'MIGRATION_EFFECTIVELY_INSTALLED'	=> 'Migration already effectively installed (skipped): %s',
	'MIGRATION_EXCEPTION_ERROR'			=> 'Something went wrong during the request and an exception was thrown. The changes made before the error occurred were reversed to the best of our abilities, but you should check the board for errors.',
	'MIGRATION_NOT_FULFILLABLE'			=> 'The migration "%1$s" is not fulfillable, missing migration "%2$s".',
	'MIGRATION_SCHEMA_DONE'				=> 'Installed Schema: %1$s; Time: %2$.2f seconds',

	'MODULE_ERROR'						=> 'An error occurred while creating a module: %s',
	'MODULE_INFO_FILE_NOT_EXIST'		=> 'A required module info file is missing: %2$s',
	'MODULE_NOT_EXIST'					=> 'A required module does not exist: %s',

	'PERMISSION_NOT_EXIST'				=> 'The permission setting "%s" unexpectedly does not exist.',

	'ROLE_NOT_EXIST'					=> 'The permission role "%s" unexpectedly does not exist.',
));
