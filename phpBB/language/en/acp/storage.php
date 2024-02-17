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

$lang = array_merge($lang, [

	// Template
	'STORAGE_TITLE'							=> 'Storage Settings',
	'STORAGE_TITLE_EXPLAIN'					=> 'Change storage providers for the file storage types of phpBB. Choose local or remote providers to store files added to or created by phpBB.',
	'STORAGE_SELECT'						=> 'Select storage',
	'STORAGE_SELECT_DESC'					=> 'Select a storage from the list.',
	'STORAGE_NAME'							=> 'Storage name',
	'STORAGE_NUM_FILES'						=> 'Number of files',
	'STORAGE_SIZE'							=> 'Size',
	'STORAGE_FREE'							=> 'Available space',
	'STORAGE_UNKNOWN'						=> 'Unknown',
	'STORAGE_UPDATE_TYPE'					=> 'Update type',
	'STORAGE_UPDATE_TYPE_CONFIG'			=> 'Update configuration only',
	'STORAGE_UPDATE_TYPE_COPY'				=> 'Update configuration and copy files',
	'STORAGE_UPDATE_TYPE_MOVE'				=> 'Update configuration and move files',

	// Template progress bar
	'STORAGE_UPDATE_IN_PROGRESS'			=> 'Storage update in progress',
	'STORAGE_UPDATE_IN_PROGRESS_EXPLAIN'	=> 'Files are being moved between storages. This can take some minutes.',

	// Storage names
	'STORAGE_ATTACHMENT_TITLE'		=> 'Attachments storage',
	'STORAGE_AVATAR_TITLE'			=> 'Avatars storage',
	'STORAGE_BACKUP_TITLE'			=> 'Backup storage',

	// Local adapter
	'STORAGE_ADAPTER_LOCAL_NAME'						=> 'Local',
	'STORAGE_ADAPTER_LOCAL_OPTION_PATH'					=> 'Path',

	// Form validation
	'STORAGE_UPDATE_SUCCESSFUL' 				=>	'All storage types were successfully updated.',
	'STORAGE_NO_CHANGES'						=>	'No changes have been applied.',
	'STORAGE_PROVIDER_NOT_EXISTS'				=>	'Provider selected for %s doesn’t exist.',
	'STORAGE_PROVIDER_NOT_AVAILABLE'			=>	'Provider selected for %s is not available.',
	'STORAGE_FORM_TYPE_EMAIL_INCORRECT_FORMAT'	=>	'Incorrect email for %s of %s.',
	'STORAGE_FORM_TYPE_TEXT_TOO_LONG'			=>	'Text is too long for %s of %s.',
	'STORAGE_FORM_TYPE_SELECT_NOT_AVAILABLE'	=>	'Selected value is not available for %s of %s.',

	'STORAGE_PATH_NOT_EXISTS'		=> '“%1$s” path does not exist or is not writable.',
	'STORAGE_PATH_NOT_SET'			=> '“%1$s” path is not set.',
]);
