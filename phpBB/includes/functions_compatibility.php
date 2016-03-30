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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Retrieve contents from remotely stored file
 *
 * @deprecated	3.1.2	Use file_downloader instead (To be removed: 3.4.0)
 */
function get_remote_file($host, $directory, $filename, &$errstr, &$errno, $port = 80, $timeout = 6)
{
	global $phpbb_container;

	// Get file downloader and assign $errstr and $errno
	/* @var $file_downloader \phpbb\file_downloader */
	$file_downloader = $phpbb_container->get('file_downloader');

	$file_data = $file_downloader->get($host, $directory, $filename, $port, $timeout);
	$errstr = $file_downloader->get_error_string();
	$errno = $file_downloader->get_error_number();

	return $file_data;
}

/**
* @return bool Always true
* @deprecated 3.2.0-dev (To be removed: 3.4.0)
*/
function phpbb_pcre_utf8_support()
{
	return true;
}


/**
 * Display reasons
 *
 * @deprecated 3.2.0-dev (To be removed: 3.4.0)
 */
function display_reasons($reason_id = 0)
{
	global $phpbb_container;

	$phpbb_container->get('phpbb.report.report_reason_list_provider')->display_reasons($reason_id);
}

/**
 * Casts a variable to the given type.
 *
 * @deprecated (To be removed: 3.4.0)
 */
function set_var(&$result, $var, $type, $multibyte = false)
{
	// no need for dependency injection here, if you have the object, call the method yourself!
	$type_cast_helper = new \phpbb\request\type_cast_helper();
	$type_cast_helper->set_var($result, $var, $type, $multibyte);
}

/**
 * Delete Attachments
 *
 * @deprecated 3.2.0-a1 (To be removed: 3.4.0)
 *
 * @param string $mode can be: post|message|topic|attach|user
 * @param mixed $ids can be: post_ids, message_ids, topic_ids, attach_ids, user_ids
 * @param bool $resync set this to false if you are deleting posts or topics
 */
function delete_attachments($mode, $ids, $resync = true)
{
	global $phpbb_container;

	/** @var \phpbb\attachment\manager $attachment_manager */
	$attachment_manager = $phpbb_container->get('attachment.manager');
	$num_deleted = $attachment_manager->delete($mode, $ids, $resync);

	unset($attachment_manager);

	return $num_deleted;
}

/**
 * Delete attached file
 *
 * @deprecated 3.2.0-a1 (To be removed: 3.4.0)
 */
function phpbb_unlink($filename, $mode = 'file', $entry_removed = false)
{
	global $phpbb_container;

	/** @var \phpbb\attachment\manager $attachment_manager */
	$attachment_manager = $phpbb_container->get('attachment.manager');
	$unlink = $attachment_manager->unlink($filename, $mode, $entry_removed);
	unset($attachment_manager);

	return $unlink;
}

/**
 * Upload Attachment - filedata is generated here
 * Uses upload class
 *
 * @deprecated 3.2.0-a1 (To be removed: 3.4.0)
 *
 * @param string			$form_name		The form name of the file upload input
 * @param int			$forum_id		The id of the forum
 * @param bool			$local			Whether the file is local or not
 * @param string			$local_storage	The path to the local file
 * @param bool			$is_message		Whether it is a PM or not
 * @param array			$local_filedata	A filespec object created for the local file
 *
 * @return array File data array
 */
function upload_attachment($form_name, $forum_id, $local = false, $local_storage = '', $is_message = false, $local_filedata = false)
{
	global $phpbb_container;

	/** @var \phpbb\attachment\manager $attachment_manager */
	$attachment_manager = $phpbb_container->get('attachment.manager');
	$file = $attachment_manager->upload($form_name, $forum_id, $local, $local_storage, $is_message, $local_filedata);
	unset($attachment_manager);

	return $file;
}
