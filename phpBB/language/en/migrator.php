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

	'MIGRATION_APPLY_DEPENDENCIES'		=> 'Apply dependencies of %s.',
	'MIGRATION_DATA_DONE'				=> 'Installed Data: %1$s; Time: %2$.2f seconds',
	'MIGRATION_DATA_IN_PROGRESS'		=> 'Installing Data: %1$s; Time: %2$.2f seconds',
	'MIGRATION_DATA_RUNNING'			=> 'Installing Data: %s.',
	'MIGRATION_EFFECTIVELY_INSTALLED'	=> 'Migration already effectively installed (skipped): %s',
	'MIGRATION_EXCEPTION_ERROR'			=> 'Something went wrong during the request and an exception was thrown. The changes made before the error occurred were reversed to the best of our abilities, but you should check the board for errors.',
	'MIGRATION_NOT_FULFILLABLE'			=> 'The migration "%1$s" is not fulfillable, missing migration "%2$s".',
	'MIGRATION_NOT_INSTALLED'			=> 'The migration "%s" is not installed.',
	'MIGRATION_NOT_VALID'				=> '%s is not a valid migration.',
	'MIGRATION_SCHEMA_DONE'				=> 'Installed Schema: %1$s; Time: %2$.2f seconds',
	'MIGRATION_SCHEMA_RUNNING'			=> 'Installing Schema: %s.',

	'MIGRATION_REVERT_DATA_DONE'		=> 'Reverted Data: %1$s; Time: %2$.2f seconds',
	'MIGRATION_REVERT_DATA_IN_PROGRESS'	=> 'Reverting Data: %1$s; Time: %2$.2f seconds',
	'MIGRATION_REVERT_DATA_RUNNING'		=> 'Reverting Data: %s.',
	'MIGRATION_REVERT_SCHEMA_DONE'		=> 'Reverted Schema: %1$s; Time: %2$.2f seconds',
	'MIGRATION_REVERT_SCHEMA_RUNNING'	=> 'Reverting Schema: %s.',

	'MIGRATION_INVALID_DATA_MISSING_CONDITION'		=> 'A migration is invalid. An if statement helper is missing a condition.',
	'MIGRATION_INVALID_DATA_MISSING_STEP'			=> 'A migration is invalid. An if statement helper is missing a valid call to a migration step.',
	'MIGRATION_INVALID_DATA_CUSTOM_NOT_CALLABLE'	=> 'A migration is invalid. A custom callable function could not be called.',
	'MIGRATION_INVALID_DATA_UNKNOWN_TYPE'			=> 'A migration is invalid. An unknown migration tool type was encountered.',
	'MIGRATION_INVALID_DATA_UNDEFINED_TOOL'			=> 'A migration is invalid. An undefined migration tool was encountered.',
	'MIGRATION_INVALID_DATA_UNDEFINED_METHOD'		=> 'A migration is invalid. An undefined migration tool method was encountered.',

	'MODULE_ERROR'						=> 'An error occurred while creating a module: %s',
	'MODULE_INFO_FILE_NOT_EXIST'		=> 'A required module info file is missing: %2$s',
	'MODULE_NOT_EXIST'					=> 'A required module does not exist: %s',

	'PERMISSION_NOT_EXIST'				=> 'The permission setting "%s" unexpectedly does not exist.',

	'ROLE_NOT_EXIST'					=> 'The permission role "%s" unexpectedly does not exist.',
));
