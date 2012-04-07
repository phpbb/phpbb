<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);


// Thank you sun.
if (isset($_SERVER['CONTENT_TYPE']))
{
	if ($_SERVER['CONTENT_TYPE'] === 'application/x-java-archive')
	{
		exit;
	}
}
else if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Java') !== false)
{
	exit;
}

if (isset($_GET['avatar']))
{
	require($phpbb_root_path . 'includes/startup.' . $phpEx);
	require($phpbb_root_path . 'config.' . $phpEx);

	if (!defined('PHPBB_INSTALLED') || empty($dbms) || empty($acm_type))
	{
		exit;
	}

	require($phpbb_root_path . 'includes/class_loader.' . $phpEx);
	require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
	require($phpbb_root_path . 'includes/constants.' . $phpEx);
	require($phpbb_root_path . 'includes/functions.' . $phpEx);
	require($phpbb_root_path . 'includes/functions_download' . '.' . $phpEx);
	require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

	$phpbb_class_loader_ext = new phpbb_class_loader('phpbb_ext_', $phpbb_root_path . 'ext/', ".$phpEx");
	$phpbb_class_loader_ext->register();
	$phpbb_class_loader = new phpbb_class_loader('phpbb_', $phpbb_root_path . 'includes/', ".$phpEx");
	$phpbb_class_loader->register();

	// set up caching
	$cache_factory = new phpbb_cache_factory($acm_type);
	$cache = $cache_factory->get_service();
	$phpbb_class_loader_ext->set_cache($cache->get_driver());
	$phpbb_class_loader->set_cache($cache->get_driver());

	$phpbb_dispatcher = new phpbb_event_dispatcher();
	$request = new phpbb_request();
	$db = new $sql_db();

	// Connect to DB
	if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false))
	{
		exit;
	}
	unset($dbpasswd);

	request_var('', 0, false, false, $request);

	// worst-case default
	$browser = strtolower($request->header('User-Agent', 'msie 6.0'));

	$config = new phpbb_config_db($db, $cache->get_driver(), CONFIG_TABLE);
	set_config(null, null, null, $config);
	set_config_count(null, null, null, $config);

	// load extensions
	$phpbb_extension_manager = new phpbb_extension_manager($db, EXT_TABLE, $phpbb_root_path, ".$phpEx", $cache->get_driver());

	$phpbb_subscriber_loader = new phpbb_event_extension_subscriber_loader($phpbb_dispatcher, $phpbb_extension_manager);
	$phpbb_subscriber_loader->load();

	$filename = request_var('avatar', '');
	$avatar_group = false;
	$exit = false;

	if (isset($filename[0]) && $filename[0] === 'g')
	{
		$avatar_group = true;
		$filename = substr($filename, 1);
	}

	// '==' is not a bug - . as the first char is as bad as no dot at all
	if (strpos($filename, '.') == false)
	{
		send_status_line(403, 'Forbidden');
		$exit = true;
	}

	if (!$exit)
	{
		$ext		= substr(strrchr($filename, '.'), 1);
		$stamp		= (int) substr(stristr($filename, '_'), 1);
		$filename	= (int) $filename;
		$exit = set_modified_headers($stamp, $browser);
	}
	if (!$exit && !in_array($ext, array('png', 'gif', 'jpg', 'jpeg')))
	{
		// no way such an avatar could exist. They are not following the rules, stop the show.
		send_status_line(403, 'Forbidden');
		$exit = true;
	}


	if (!$exit)
	{
		if (!$filename)
		{
			// no way such an avatar could exist. They are not following the rules, stop the show.
			send_status_line(403, 'Forbidden');
		}
		else
		{
			send_avatar_to_browser(($avatar_group ? 'g' : '') . $filename . '.' . $ext, $browser);
		}
	}
	file_gc();
}

// implicit else: we are not in avatar mode
include($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_download' . '.' . $phpEx);

$download_id = request_var('id', 0);
$mode = request_var('mode', '');
$thumbnail = request_var('t', false);

// Start session management, do not update session page.
$user->session_begin(false);
$auth->acl($user->data);
$user->setup('viewtopic');

if (!$download_id)
{
	send_status_line(404, 'Not Found');
	trigger_error('NO_ATTACHMENT_SELECTED');
}

if (!$config['allow_attachments'] && !$config['allow_pm_attach'])
{
	send_status_line(404, 'Not Found');
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}

$sql = 'SELECT attach_id, in_message, post_msg_id, extension, is_orphan, poster_id, filetime
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE attach_id = $download_id";
$result = $db->sql_query_limit($sql, 1);
$attachment = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$attachment)
{
	send_status_line(404, 'Not Found');
	trigger_error('ERROR_NO_ATTACHMENT');
}

if ((!$attachment['in_message'] && !$config['allow_attachments']) || ($attachment['in_message'] && !$config['allow_pm_attach']))
{
	send_status_line(404, 'Not Found');
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}

$row = array();

if ($attachment['is_orphan'])
{
	// We allow admins having attachment permissions to see orphan attachments...
	$own_attachment = ($auth->acl_get('a_attach') || $attachment['poster_id'] == $user->data['user_id']) ? true : false;

	if (!$own_attachment || ($attachment['in_message'] && !$auth->acl_get('u_pm_download')) || (!$attachment['in_message'] && !$auth->acl_get('u_download')))
	{
		send_status_line(404, 'Not Found');
		trigger_error('ERROR_NO_ATTACHMENT');
	}

	// Obtain all extensions...
	$extensions = $cache->obtain_attach_extensions(true);
}
else
{
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

		$f_download = $auth->acl_get('f_download', $row['forum_id']);

		if ($auth->acl_get('u_download') && $f_download)
		{
			if ($row && $row['forum_password'])
			{
				// Do something else ... ?
				login_forum_box($row);
			}
		}
		else
		{
			send_status_line(403, 'Forbidden');
			trigger_error('SORRY_AUTH_VIEW_ATTACH');
		}
	}
	else
	{
		$row['forum_id'] = false;
		if (!$auth->acl_get('u_pm_download'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error('SORRY_AUTH_VIEW_ATTACH');
		}

		// Check if the attachment is within the users scope...
		$sql = 'SELECT user_id, author_id
			FROM ' . PRIVMSGS_TO_TABLE . '
			WHERE msg_id = ' . $attachment['post_msg_id'];
		$result = $db->sql_query($sql);

		$allowed = false;
		while ($user_row = $db->sql_fetchrow($result))
		{
			if ($user->data['user_id'] == $user_row['user_id'] || $user->data['user_id'] == $user_row['author_id'])
			{
				$allowed = true;
				break;
			}
		}
		$db->sql_freeresult($result);

		if (!$allowed)
		{
			send_status_line(403, 'Forbidden');
			trigger_error('ERROR_NO_ATTACHMENT');
		}
	}

	// disallowed?
	$extensions = array();
	if (!extension_allowed($row['forum_id'], $attachment['extension'], $extensions))
	{
		send_status_line(404, 'Forbidden');
		trigger_error(sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
	}
}

if (!download_allowed())
{
	send_status_line(403, 'Forbidden');
	trigger_error($user->lang['LINKAGE_FORBIDDEN']);
}

$download_mode = (int) $extensions[$attachment['extension']]['download_mode'];

// Fetching filename here to prevent sniffing of filename
$sql = 'SELECT attach_id, is_orphan, in_message, post_msg_id, extension, physical_filename, real_filename, mimetype, filesize, filetime
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE attach_id = $download_id";
$result = $db->sql_query_limit($sql, 1);
$attachment = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$attachment)
{
	send_status_line(404, 'Not Found');
	trigger_error('ERROR_NO_ATTACHMENT');
}

$attachment['physical_filename'] = utf8_basename($attachment['physical_filename']);
$display_cat = $extensions[$attachment['extension']]['display_cat'];

if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !$user->optionget('viewimg'))
{
	$display_cat = ATTACHMENT_CATEGORY_NONE;
}

if ($display_cat == ATTACHMENT_CATEGORY_FLASH && !$user->optionget('viewflash'))
{
	$display_cat = ATTACHMENT_CATEGORY_NONE;
}

if ($thumbnail)
{
	$attachment['physical_filename'] = 'thumb_' . $attachment['physical_filename'];
}
else if (($display_cat == ATTACHMENT_CATEGORY_NONE/* || $display_cat == ATTACHMENT_CATEGORY_IMAGE*/) && !$attachment['is_orphan'] && !phpbb_http_byte_range($attachment['filesize']))
{
	// Update download count
	$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
		SET download_count = download_count + 1
		WHERE attach_id = ' . $attachment['attach_id'];
	$db->sql_query($sql);
}

if ($display_cat == ATTACHMENT_CATEGORY_IMAGE && $mode === 'view' && (strpos($attachment['mimetype'], 'image') === 0) && ((strpos(strtolower($user->browser), 'msie') !== false) && (strpos(strtolower($user->browser), 'msie 8.0') === false)))
{
	wrap_img_in_html(append_sid($phpbb_root_path . 'download/file.' . $phpEx, 'id=' . $attachment['attach_id']), $attachment['real_filename']);
	file_gc();
}
else
{
	// Determine the 'presenting'-method
	if ($download_mode == PHYSICAL_LINK)
	{
		// This presenting method should no longer be used
		if (!@is_dir($phpbb_root_path . $config['upload_path']))
		{
			send_status_line(500, 'Internal Server Error');
			trigger_error($user->lang['PHYSICAL_DOWNLOAD_NOT_POSSIBLE']);
		}

		redirect($phpbb_root_path . $config['upload_path'] . '/' . $attachment['physical_filename']);
		file_gc();
	}
	else
	{
		send_file_to_browser($attachment, $config['upload_path'], $display_cat);
		file_gc();
	}
}
