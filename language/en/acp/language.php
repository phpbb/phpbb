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
	'ACP_FILES'						=> 'Admin language files',
	'ACP_LANGUAGE_PACKS_EXPLAIN'	=> 'Here you are able to install/remove language packs. The default language pack is marked with an asterisk (*).',

	'DELETE_LANGUAGE_CONFIRM'		=> 'Are you sure you wish to delete “%s”?',

	'INSTALLED_LANGUAGE_PACKS'		=> 'Installed language packs',

	'LANGUAGE_DETAILS_UPDATED'			=> 'Language details successfully updated.',
	'LANGUAGE_PACK_ALREADY_INSTALLED'	=> 'This language pack is already installed.',
	'LANGUAGE_PACK_DELETED'				=> 'The language pack “%s” has been removed successfully. All users using this language have been reset to the board’s default language.',
	'LANGUAGE_PACK_DETAILS'				=> 'Language pack details',
	'LANGUAGE_PACK_INSTALLED'			=> 'The language pack “%s” has been successfully installed.',
	'LANGUAGE_PACK_CPF_UPDATE'			=> 'The custom profile fields’ language strings were copied from the default language. Please change them if necessary.',
	'LANGUAGE_PACK_ISO'					=> 'ISO',
	'LANGUAGE_PACK_LOCALNAME'			=> 'Local name',
	'LANGUAGE_PACK_NAME'				=> 'Name',
	'LANGUAGE_PACK_NOT_EXIST'			=> 'The selected language pack does not exist.',
	'LANGUAGE_PACK_USED_BY'				=> 'Used by (including robots)',
	'LANGUAGE_VARIABLE'					=> 'Language variable',
	'LANG_AUTHOR'						=> 'Language pack author',
	'LANG_ENGLISH_NAME'					=> 'English name',
	'LANG_ISO_CODE'						=> 'ISO code',
	'LANG_LOCAL_NAME'					=> 'Local name',

	'MISSING_LANG_FILES'		=> 'Missing language files',
	'MISSING_LANG_VARIABLES'	=> 'Missing language variables',

	'NO_FILE_SELECTED'				=> 'You haven’t specified a language file.',
	'NO_LANG_ID'					=> 'You haven’t specified a language pack.',
	'NO_REMOVE_DEFAULT_LANG'		=> 'You are not able to remove the default language pack.<br />If you want to remove this language pack, change your board’s default language first.',
	'NO_UNINSTALLED_LANGUAGE_PACKS'	=> 'No uninstalled language packs',

	'THOSE_MISSING_LANG_FILES'			=> 'The following language files are missing from the “%s” language folder',
	'THOSE_MISSING_LANG_VARIABLES'		=> 'The following language variables are missing from the “%s” language pack',

	'UNINSTALLED_LANGUAGE_PACKS'	=> 'Uninstalled language packs',

	'BROWSE_LANGUAGE_PACKS_DATABASE'	=> 'Browse language packs database',
));
