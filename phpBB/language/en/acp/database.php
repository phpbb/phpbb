<?php
/** 
*
* acp_database [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

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

// Banning
$lang = array_merge($lang, array(
	'DATABASE' => 'Database Utilities',
	'ACP_BACKUP_EXPLAIN'	=> 'Here you can backup all your phpBB related data. You may store the resulting archive in your store/ folder or download it directly. Depending on your server configuration you be able to compress the file in a number of formats. If you wish to include any additional "custom" tables please list them in the additional tables field, separated by commas. ',
	'BACKUP_OPTIONS'	=> 'Backup options',
	'BACKUP_TYPE'		=> 'Backup type',
	'BACKUP_INVALID'	=> 'The selected file to backup is invalid',
	'START_BACKUP'		=> 'Start Backup',
	'FULL_BACKUP'		=> 'Full',
	'STRUCTURE_ONLY'	=> 'Structure Only',
	'DATA_ONLY'			=> 'Data only',
	'TABLE_SELECT'		=> 'Table Select',
	'FILE_TYPE'			=> 'File Type',
	'STORE_LOCAL'		=> 'Store file locally',
	'SELECT_ALL'		=> 'Select all',
	'DESELECT_ALL'		=> 'Deselect all',
	'BACKUP_SUCCESS'	=> 'The backup file has been created successfully',
	'BACKUP_DELETE'		=> 'The backup file has been deleted successfully',

	'STORE_AND_DOWNLOAD'	=> 'Store and Download',
	'ACP_RESTORE_EXPLAIN'	=> 'This will perform a full restore of all phpBB tables from a saved file. If your server supports it you may use a gzip or bzip2 compressed text file and it will automatically be decompressed. <b>WARNING</b> This will overwrite any existing data. The restore may take a long time to process please do not move from this page till it is complete.',
	'SELECT_FILE'			=> 'Select a file',
	'RESTORE_OPTIONS'		=> 'Restore options',
	'START_RESTORE'			=> 'Start Restore',
	'DELETE_BACKUP'			=> 'Delete Backup',
	'DOWNLOAD_BACKUP'		=> 'Download Backup',
	'RESTORE_SUCCESS'		=> 'The Database has been successfully restored.<br /><br />Your board should be back to the state it was when the backup was made.',
));

?>