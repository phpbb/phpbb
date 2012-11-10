<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewtopic');

$file = request_var('file', '');
$filename = $phpbb_root_path . 'umil/error_files/' . $file . '.txt';

if ($user->data['user_type'] != USER_FOUNDER || // Only founders can access this.
	!$file || // Do we have a file name?
	strpos($file, '/') || strpos($file, '.')) // Make sure they are not attempting to grab files outside of the umil/error_files/ directory
{
	header('HTTP/1.0 403 Forbidden');
	trigger_error($user->lang['LINKAGE_FORBIDDEN']);
}

// Check if headers already sent or not able to get the file contents.
if (headers_sent() || !@file_exists($filename) || !@is_readable($filename))
{
	// PHP track_errors setting On?
	if (!empty($php_errormsg))
	{
		trigger_error($user->lang['UNABLE_TO_DELIVER_FILE'] . '<br />' . sprintf($user->lang['TRACKED_PHP_ERROR'], $php_errormsg));
	}

	trigger_error('UNABLE_TO_DELIVER_FILE');
}

header('Content-type: text/plain');
header('Content-Disposition: filename="' . $file . '.txt"');

$size = @filesize($filename);
if ($size)
{
	header("Content-Length: $size");
}

$fp = @fopen($filename, 'rb');
if ($fp !== false)
{
	while (!feof($fp))
	{
		echo fread($fp, 8192);
	}
	fclose($fp);
}

garbage_collection();
exit_handler();
?>