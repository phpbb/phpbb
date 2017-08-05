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

	// Template
	'STORAGE_TITLE'					=> 'Storage Settings',
	'STORAGE_TITLE_EXPLAIN'			=> 'Here you can change the storage.',
	'STORAGE_SELECT'				=> 'Select storage',
	'STORAGE_SELECT_DESC'			=> 'Select an storage from the list.',

	// Storage ma,es
	'STORAGE_ATTACHMENT_TITLE'		=> 'Attachments storage',
	'STORAGE_AVATAR_TITLE'			=> 'Avatars storage',
	'STORAGE_BACKUP_TITLE'			=> 'Backup storage',

	// Local adapter
	'STORAGE_ADAPTER_LOCAL_NAME'			=> 'Local',
	'STORAGE_ADAPTER_LOCAL_OPTION_PATH'		=> 'Path',

	// Form validation
	'STORAGE_UPDATE_SUCCESSFUL' 				=>	'All storages were successfuly updated.',
	'STORAGE_NO_CHANGES'						=>	'No changes has been made.',
	'STORAGE_PROVIDER_NOT_EXISTS'				=>	'Provider selected for %s doesn\'t exist.',
	'STORAGE_PROVIDER_NOT_AVAILABLE'			=>	'Provider selected for %s is not available.',
	'STORAGE_FORM_TYPE_EMAIL_INCORRECT_FORMAT'	=>	'Incorrect email for %s of %s.',
	'STORAGE_FORM_TYPE_TEXT_TOO_LONG'			=>	'Text is too long for %s of %s.',
	'STORAGE_FORM_TYPE_SELECT_NOT_AVAILABLE'	=>	'Selected value is not available for %s of %s.',
));
