<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 */

/**
 * @ignore
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACTION'						=> 'Action',
	'ADVANCED'						=> 'Advanced',
	'AUTH_CACHE_PURGE'				=> 'Purging the Auth Cache',

	'CACHE_PURGE'					=> 'Purging your forum’s cache',
	'CONFIGURE'						=> 'Configure',
	'CONFIG_ADD'					=> 'Adding new config variable: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'ERROR: Config variable %s already exists.',
	'CONFIG_NOT_EXIST'				=> 'ERROR: Config variable %s does not exist.',
	'CONFIG_REMOVE'					=> 'Removing config variable: %s',
	'CONFIG_UPDATE'					=> 'Updating config variable: %s',

	'DISPLAY_RESULTS'				=> 'Display Full Results',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Select yes to display all of the actions and results during the requested action.',

	'ERROR_NOTICE'					=> 'One or more errors occured during the requested action.  Please download <a href="%1$s">this file</a> with the errors listed in it and ask the mod author for assistance.<br /><br />If you have any problem downloading that file you may access it directly with an FTP browser at the following location: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'One or more errors occured during the requested action.  Please make a full record of any errors and ask the mod author for assistance.',

	'FAIL'							=> 'Fail',
	'FILE_COULD_NOT_READ'			=> 'ERROR: Could not open the file %s for reading.',
	'FOUNDERS_ONLY'					=> 'You must be a board founder to access this page.',

	'GROUP_NOT_EXIST'				=> 'Group does not exist',

	'IGNORE'						=> 'Ignore',
	'IMAGESET_CACHE_PURGE'			=> 'Refreshing the %s imageset',
	'INSTALL'						=> 'Install',
	'INSTALL_MOD'					=> 'Install %s',
	'INSTALL_MOD_CONFIRM'			=> 'Are you ready to install %s?',

	'MODULE_ADD'					=> 'Adding %1$s module: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'ERROR: Module already exists.',
	'MODULE_NOT_EXIST'				=> 'ERROR: Module does not exist.',
	'MODULE_REMOVE'					=> 'Removing %1$s module: %2$s',

	'NONE'							=> 'None',
	'NO_TABLE_DATA'					=> 'ERROR: No table data was specified',

	'PARENT_NOT_EXIST'				=> 'ERROR: The parent category specified for this module does not exist.',
	'PERMISSIONS_WARNING'			=> 'New permission settings have been added.  Be sure to check your permission settings and see that they are as you would like them.',
	'PERMISSION_ADD'				=> 'Adding new permission option: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'ERROR: Permission option %s already exists.',
	'PERMISSION_NOT_EXIST'			=> 'ERROR: Permission option %s does not exist.',
	'PERMISSION_REMOVE'				=> 'Removing permission option: %s',
	'PERMISSION_ROLE_ADD'			=> 'Adding new permission role: %s',
	'PERMISSION_ROLE_UPDATE'		=> 'Updating permission role: %s',
	'PERMISSION_ROLE_REMOVE'		=> 'Removing permission role: %s',
	'PERMISSION_SET_GROUP'			=> 'Setting permissions for the %s group.',
	'PERMISSION_SET_ROLE'			=> 'Setting permissions for the %s role.',
	'PERMISSION_UNSET_GROUP'		=> 'Unsetting permissions for the %s group.',
	'PERMISSION_UNSET_ROLE'			=> 'Unsetting permissions for the %s role.',

	'ROLE_ALREADY_EXISTS'			=> 'Permission role already exists.',
	'ROLE_NOT_EXIST'				=> 'Permission role does not exist',

	'SUCCESS'						=> 'Success',

	'TABLE_ADD'						=> 'Adding a new database table: %s',
	'TABLE_ALREADY_EXISTS'			=> 'ERROR: Database table %s already exists.',
	'TABLE_COLUMN_ADD'				=> 'Adding a new column named %2$s to table %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'ERROR: The column %2$s already exists on table %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'ERROR: The column %2$s does not exist on table %1$s.',
	'TABLE_COLUMN_REMOVE'			=> 'Removing the column named %2$s from table %1$s',
	'TABLE_COLUMN_UPDATE'			=> 'Updating a column named %2$s from table %1$s',
	'TABLE_KEY_ADD'					=> 'Adding a key named %2$s to table %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'ERROR: The index %2$s already exists on table %1$s.',
	'TABLE_KEY_NOT_EXIST'			=> 'ERROR: The index %2$s does not exist on table %1$s.',
	'TABLE_KEY_REMOVE'				=> 'Removing a key named %2$s from table %1$s',
	'TABLE_NOT_EXIST'				=> 'ERROR: Database table %s does not exist.',
	'TABLE_REMOVE'					=> 'Removing database table: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Inserting data in the %s database table.',
	'TABLE_ROW_REMOVE_DATA'			=> 'Removing a row from the %s database table',
	'TABLE_ROW_UPDATE_DATA'			=> 'Updating a row in the %s database table.',
	'TEMPLATE_CACHE_PURGE'			=> 'Refreshing the %s template',
	'THEME_CACHE_PURGE'				=> 'Refreshing the %s theme',

	'UNINSTALL'						=> 'Uninstall',
	'UNINSTALL_MOD'					=> 'Uninstall %s',
	'UNINSTALL_MOD_CONFIRM'			=> 'Are you ready to uninstall %s?  All settings and data saved by this mod will be removed!',
	'UNKNOWN'						=> 'Unknown',
	'UPDATE_MOD'					=> 'Update %s',
	'UPDATE_MOD_CONFIRM'			=> 'Are you ready to update %s?',
	'UPDATE_UMIL'					=> 'This version of UMIL is outdated.<br /><br />Please download the latest UMIL (Unified MOD Install Library) from: <a href="%1$s" target="_blank">%1$s</a>',

	'VERSIONS'						=> 'Mod Version: <strong>%1$s</strong><br />Currently Installed: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Version Select',
	'VERSION_SELECT_EXPLAIN'		=> 'Do not change from “Ignore” unless you know what you are doing or were told to.',
));

?>