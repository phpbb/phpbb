<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$download_id = request_var('id', 0);
$thumbnail = request_var('t', false);

// Start session management, do not update session page.
$user->session_begin(false);
$auth->acl($user->data);
$user->setup('viewtopic');

if (!$download_id)
{
	trigger_error('NO_ATTACHMENT_SELECTED');
}

if (!$config['allow_attachments'] && !$config['allow_pm_attach'])
{
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}

$sql = 'SELECT attach_id, in_message, post_msg_id, extension
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE attach_id = $download_id";
$result = $db->sql_query_limit($sql, 1);
$attachment = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$attachment)
{
	trigger_error('ERROR_NO_ATTACHMENT');
}

if ((!$attachment['in_message'] && !$config['allow_attachments']) || ($attachment['in_message'] && !$config['allow_pm_attach']))
{
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}

$row = array();
if (!$attachment['in_message'])
{
	// 
	$sql = 'SELECT p.forum_id, f.forum_password, f.parent_id
		FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f
		WHERE p.post_id = ' . $attachment['post_msg_id'] . '
			AND p.forum_id = f.forum_id';
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	// Global announcement?
	if (!$row)
	{
		$forum_id = request_var('f', 0);

		$sql = 'SELECT forum_id, forum_password, parent_id
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $forum_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	}

	if ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']))
	{
		if ($row['forum_password'])
		{
			// Do something else ... ?
			login_forum_box($row);
		}
	}
	else
	{
		trigger_error('SORRY_AUTH_VIEW_ATTACH');
	}
}
else
{
	$row['forum_id'] = 0;
	if (!$auth->acl_get('u_pm_download'))
	{
		trigger_error('SORRY_AUTH_VIEW_ATTACH');
	}
}

// disallowed ?
$extensions = array();
if (!extension_allowed($row['forum_id'], $attachment['extension'], $extensions))
{
	trigger_error(sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
}

if (!download_allowed())
{
	trigger_error($user->lang['LINKAGE_FORBIDDEN']);
}

$download_mode = (int) $extensions[$attachment['extension']]['download_mode'];

// Fetching filename here to prevent sniffing of filename
$sql = 'SELECT attach_id, in_message, post_msg_id, extension, physical_filename, real_filename, mimetype
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE attach_id = $download_id";
$result = $db->sql_query_limit($sql, 1);
$attachment = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$attachment)
{
	trigger_error('ERROR_NO_ATTACHMENT');
}


$attachment['physical_filename'] = basename($attachment['physical_filename']);
$display_cat = $extensions[$attachment['extension']]['display_cat'];

if ($thumbnail)
{
	$attachment['physical_filename'] = 'thumb_' . $attachment['physical_filename'];
}
else if ($display_cat == ATTACHMENT_CATEGORY_NONE)
{
	// Update download count
	$sql = 'UPDATE ' . ATTACHMENTS_TABLE . ' 
		SET download_count = download_count + 1 
		WHERE attach_id = ' . $attachment['attach_id'];
	$db->sql_query($sql);
}

// Determine the 'presenting'-method
if ($download_mode == PHYSICAL_LINK)
{
	if (!@is_dir($phpbb_root_path . $config['upload_path']))
	{
		trigger_error($user->lang['PHYSICAL_DOWNLOAD_NOT_POSSIBLE']);
	}

	redirect($phpbb_root_path . $config['upload_path'] . '/' . $attachment['physical_filename']);
	exit;
}
else
{
	send_file_to_browser($attachment, $config['upload_path'], $extensions[$attachment['extension']]['display_cat']);
	exit;
}


/**
* Send file to browser
*/
function send_file_to_browser($attachment, $upload_dir, $category)
{
	global $user, $db, $config, $phpbb_root_path;

	$filename = $phpbb_root_path . $upload_dir . '/' . $attachment['physical_filename'];

	if (!@file_exists($filename))
	{
		trigger_error($user->lang['ERROR_NO_ATTACHMENT'] . '<br /><br />' . sprintf($user->lang['FILE_NOT_FOUND_404'], $filename));
	}

	// Correct the mime type - we force application/octetstream for all files, except images
	// Please do not change this, it is a security precaution
	if ($category == ATTACHMENT_CATEGORY_NONE && strpos($attachment['mimetype'], 'image') === false)
	{
		$attachment['mimetype'] = (strpos(strtolower($user->browser), 'msie') !== false || strpos(strtolower($user->browser), 'opera') !== false) ? 'application/octetstream' : 'application/octet-stream';
	}

	if (@ob_get_length())
	{
		@ob_end_clean();
	}

	// Now send the File Contents to the Browser
	$size = @filesize($filename);

	// Might not be ideal to store the contents, but file_get_contents is binary-safe as well as the recommended method
	// To correctly display further errors we need to make sure we are using the correct headers for both (unsetting content-length may not work)
	$contents = @file_get_contents($filename);

	// Check if headers already sent or not able to get the file contents.
	if (headers_sent() || $contents === false)
	{
		unset($contents);

		// PHP track_errors setting On?
		if (!empty($php_errormsg))
		{
			trigger_error($user->lang['UNABLE_TO_DELIVER_FILE'] . '<br />' . sprintf($user->lang['TRACKED_PHP_ERROR'], $php_errormsg));
		}

		trigger_error('UNABLE_TO_DELIVER_FILE');
	}

	// Now the tricky part... let's dance
	header('Pragma: public');

	// Send out the Headers
	header('Content-type: ' . $attachment['mimetype'] . '; name="' . $attachment['real_filename'] . '"');
	header('Content-Disposition: inline; filename="' . $attachment['real_filename'] . '"');

	if ($size)
	{
		header("Content-length: $size");
	}
	echo $contents;
	unset($contents);

	flush();
	exit;
}

/**
* Check if downloading item is allowed
*/
function download_allowed()
{
	global $config, $user, $db;

	if (!$config['secure_downloads'])
	{
		return true;
	}

	$url = (!empty($_SERVER['HTTP_REFERER'])) ? trim($_SERVER['HTTP_REFERER']) : trim(getenv('HTTP_REFERER'));

	if (!$url)
	{
		return ($config['secure_allow_empty_referer']) ? true : false;
	}

	// Split URL into domain and script part
	$url = @parse_url($url);

	if ($url === false)
	{
		return ($config['secure_allow_empty_referer']) ? true : false;
	}

	$hostname = $url['host'];
	unset($url);

	$allowed = ($config['secure_allow_deny']) ? false : true;
	$iplist = array();

	if (($ip_ary = @gethostbynamel($hostname)) !== false)
	{
		foreach ($ip_ary as $ip)
		{
			if ($ip)
			{
				$iplist[] = $ip;
			}
		}
	}
	
	// Check for own server...
	$server_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME');

	// Forcing server vars is the only way to specify/override the protocol
	if ($config['force_server_vars'] || !$server_name)
	{
		$server_name = $config['server_name'];
	}

	if (preg_match('#^.*?' . preg_quote($server_name, '#') . '.*?$#i', $hostname))
	{
		$allowed = true;
	}
	
	// Get IP's and Hostnames
	if (!$allowed)
	{
		$sql = 'SELECT site_ip, site_hostname, ip_exclude
			FROM ' . SITELIST_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$site_ip = trim($row['site_ip']);
			$site_hostname = trim($row['site_hostname']);

			if ($site_ip)
			{
				foreach ($iplist as $ip)
				{
					if (preg_match('#^' . str_replace('*', '.*?', preg_quote($site_ip, '#')) . '$#i', $ip))
					{
						if ($row['ip_exclude'])
						{
							$allowed = ($config['secure_allow_deny']) ? false : true;
							break 2;
						}
						else
						{
							$allowed = ($config['secure_allow_deny']) ? true : false;
						}
					}
				}
			}

			if ($site_hostname)
			{
				if (preg_match('#^' . str_replace('*', '.*?', preg_quote($site_hostname, '#')) . '$#i', $hostname))
				{
					if ($row['ip_exclude'])
					{
						$allowed = ($config['secure_allow_deny']) ? false : true;
						break;
					}
					else
					{
						$allowed = ($config['secure_allow_deny']) ? true : false;
					}
				}
			}
		}
		$db->sql_freeresult($result);
	}
	
	return $allowed;
}

?>