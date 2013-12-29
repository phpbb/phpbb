<?php
/**
*
* plupload [English]
*
* @package language
* @copyright (c) 2010-2013 Moxiecode Systems AB
* @copyright (c) 2012 phpBB Group
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
	'PLUPLOAD_ADD_FILES'		=> 'Add files',
	'PLUPLOAD_ADD_FILES_TO_QUEUE'	=> 'Add files to the upload queue and click the start button.',
	'PLUPLOAD_ALREADY_QUEUED'	=> '%s already present in the queue.',
	'PLUPLOAD_CLOSE'			=> 'Close',
	'PLUPLOAD_DRAG'				=> 'Drag files here.',
	'PLUPLOAD_DUPLICATE_ERROR'	=> 'Duplicate file error.',
	'PLUPLOAD_ERR_INPUT'		=> 'Failed to open input stream.',
	'PLUPLOAD_ERR_MOVE_UPLOADED'	=> 'Failed to move uploaded file.',
	'PLUPLOAD_ERR_OUTPUT'		=> 'Failed to open output stream.',
	'PLUPLOAD_ERR_FILE_TOO_LARGE'	=> 'Error: File too large:',
	'PLUPLOAD_ERR_FILE_COUNT'	=> 'File count error.',
	'PLUPLOAD_ERR_FILE_INVALID_EXT'	=> 'Error: Invalid file extension:',
	'PLUPLOAD_ERR_RUNTIME_MEMORY'	=> 'Runtime ran out of available memory.',
	'PLUPLOAD_ERR_UPLOAD_LIMIT'	=> 'Upload element accepts only %d file(s) at a time. Extra files were stripped.',
	'PLUPLOAD_ERR_UPLOAD_URL'	=> 'Upload URL might be wrong or does not exist.',
	'PLUPLOAD_EXTENSION_ERROR'	=> 'File extension error.',
	'PLUPLOAD_FILE'				=> 'File: %s',
	'PLUPLOAD_FILE_DETAILS'		=> 'File: %s, size: %d, max file size: %d',
	'PLUPLOAD_FILENAME'			=> 'Filename',
	'PLUPLOAD_FILES_QUEUED'		=> '%d files queued',
	'PLUPLOAD_GENERIC_ERROR'	=> 'Generic error.',
	'PLUPLOAD_HTTP_ERROR'		=> 'HTTP error.',
	'PLUPLOAD_IMAGE_FORMAT'		=> 'Image format either wrong or not supported.',
	'PLUPLOAD_INIT_ERROR'		=> 'Init error.',
	'PLUPLOAD_IO_ERROR'			=> 'IO error.',
	'PLUPLOAD_NOT_APPLICABLE'	=> 'N/A',
	'PLUPLOAD_SECURITY_ERROR'	=> 'Security error.',
	'PLUPLOAD_SELECT_FILES'		=> 'Select files',
	'PLUPLOAD_SIZE'				=> 'Size',
	'PLUPLOAD_SIZE_ERROR'		=> 'File size error.',
	'PLUPLOAD_STATUS'			=> 'Status',
	'PLUPLOAD_START_UPLOAD'		=> 'Start upload',
	'PLUPLOAD_START_CURRENT_UPLOAD'	=> 'Start uploading queue',
	'PLUPLOAD_STOP_UPLOAD'		=> 'Stop upload',
	'PLUPLOAD_STOP_CURRENT_UPLOAD'	=> 'Stop current upload',
	// Note: This string is formatted independently by plupload and so does not
	// use the same formatting rules as normal phpBB translation strings
	'PLUPLOAD_UPLOADED'			=> 'Uploaded %d/%d files',
));
