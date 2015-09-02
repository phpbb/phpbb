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
	'CLI_CONFIG_CANNOT_CACHED'			=> 'Set this option if the configuration option changes too frequently to be efficiently cached.',
	'CLI_CONFIG_CURRENT'				=> 'Current configuration value, use 0 and 1 to specify boolean values',
	'CLI_CONFIG_DELETE_SUCCESS'			=> 'Successfully deleted config %s.',
	'CLI_CONFIG_NEW'					=> 'New configuration value, use 0 and 1 to specify boolean values',
	'CLI_CONFIG_NOT_EXISTS'				=> 'Config %s does not exist',
	'CLI_CONFIG_OPTION_NAME'			=> 'The configuration option’s name',
	'CLI_CONFIG_PRINT_WITHOUT_NEWLINE'	=> 'Set this option if the value should be printed without a new line at the end.',
	'CLI_CONFIG_INCREMENT_BY'			=> 'Amount to increment by',
	'CLI_CONFIG_INCREMENT_SUCCESS'		=> 'Successfully incremented config %s',
	'CLI_CONFIG_SET_FAILURE'			=> 'Could not set config %s',
	'CLI_CONFIG_SET_SUCCESS'			=> 'Successfully set config %s',

	'CLI_DESCRIPTION_CRON_LIST'					=> 'Prints a list of ready and unready cron jobs.',
	'CLI_DESCRIPTION_CRON_RUN'					=> 'Runs all ready cron tasks.',
	'CLI_DESCRIPTION_CRON_RUN_ARGUMENT_1'		=> 'Name of the task to be run',
	'CLI_DESCRIPTION_DB_MIGRATE'				=> 'Updates the database by applying migrations.',
	'CLI_DESCRIPTION_DELETE_CONFIG'				=> 'Deletes a configuration option',
	'CLI_DESCRIPTION_DISABLE_EXTENSION'			=> 'Disables the specified extension.',
	'CLI_DESCRIPTION_ENABLE_EXTENSION'			=> 'Enables the specified extension.',
	'CLI_DESCRIPTION_FIND_MIGRATIONS'			=> 'Finds migrations that are not depended upon.',
	'CLI_DESCRIPTION_GET_CONFIG'				=> 'Gets a configuration option’s value',
	'CLI_DESCRIPTION_INCREMENT_CONFIG'			=> 'Increments a configuration option’s integer value',
	'CLI_DESCRIPTION_LIST_EXTENSIONS'			=> 'Lists all extensions in the database and on the filesystem.',
	'CLI_DESCRIPTION_OPTION_SAFE_MODE'			=> 'Run in Safe Mode (without extensions).',
	'CLI_DESCRIPTION_OPTION_SHELL'				=> 'Launch the shell.',
	'CLI_DESCRIPTION_PURGE_EXTENSION'			=> 'Purges the specified extension.',
	'CLI_DESCRIPTION_RECALCULATE_EMAIL_HASH'	=> 'Recalculates the user_email_hash column of the users table.',
	'CLI_DESCRIPTION_SET_ATOMIC_CONFIG'			=> 'Sets a configuration option’s value only if the old matches the current value',
	'CLI_DESCRIPTION_SET_CONFIG'				=> 'Sets a configuration option’s value',

	'CLI_EXTENSION_DISABLE_FAILURE'		=> 'Could not disable extension %s',
	'CLI_EXTENSION_DISABLE_SUCCESS'		=> 'Successfully disabled extension %s',
	'CLI_EXTENSION_ENABLE_FAILURE'		=> 'Could not enable extension %s',
	'CLI_EXTENSION_ENABLE_SUCCESS'		=> 'Successfully enabled extension %s',
	'CLI_EXTENSION_NAME'				=> 'Name of the extension',
	'CLI_EXTENSION_PURGE_FAILURE'		=> 'Could not purge extension %s',
	'CLI_EXTENSION_PURGE_SUCCESS'		=> 'Successfully purged extension %s',
	'CLI_EXTENSION_NOT_FOUND'			=> 'No extensions were found.',
	'CLI_EXTENSIONS_AVAILABLE'			=> 'Available',
	'CLI_EXTENSIONS_DISABLED'			=> 'Disabled',
	'CLI_EXTENSIONS_ENABLED'			=> 'Enabled',

	'CLI_FIXUP_RECALCULATE_EMAIL_HASH_SUCCESS'	=> 'Successfully recalculated all email hashes.',
));

// Additional help for commands.
$lang = array_merge($lang, array(
	'CLI_HELP_CRON_RUN'			=> $lang['CLI_DESCRIPTION_CRON_RUN'] . ' Optionally you can specify a cron task name to run only the specified cron task.',
));
