<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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

	require($phpbb_root_path . 'includes/constants.' . $phpEx);
	require($phpbb_root_path . 'includes/functions.' . $phpEx);
	require($phpbb_root_path . 'includes/functions_container.' . $phpEx);
	require($phpbb_root_path . 'includes/functions_download' . '.' . $phpEx);
	require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

	// Setup class loader first
	$phpbb_class_loader = new \phpbb\class_loader('phpbb_', "{$phpbb_root_path}includes/", $phpEx);
	$phpbb_class_loader->register();
	$phpbb_class_loader_ext = new \phpbb\class_loader('phpbb_ext_', "{$phpbb_root_path}ext/", $phpEx);
	$phpbb_class_loader_ext->register();

	// Set up container
	$phpbb_container = phpbb_create_default_container($phpbb_root_path, $phpEx);

	$phpbb_class_loader->set_cache($phpbb_container->get('cache.driver'));
	$phpbb_class_loader_ext->set_cache($phpbb_container->get('cache.driver'));

	// set up caching
	$cache = $phpbb_container->get('cache');

	$phpbb_dispatcher = $phpbb_container->get('dispatcher');
	$request	= $phpbb_container->get('request');
	$db			= $phpbb_container->get('dbal.conn');
	$phpbb_log	= $phpbb_container->get('log');

	// Connect to DB
	if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false))
	{
		exit;
	}
	unset($dbpasswd);

	request_var('', 0, false, false, $request);

	$config = $phpbb_container->get('config');
	set_config(null, null, null, $config);
	set_config_count(null, null, null, $config);

	// load extensions
	$phpbb_extension_manager = $phpbb_container->get('ext.manager');
	$phpbb_subscriber_loader = $phpbb_container->get('event.subscriber_loader');

	// worst-case default
	$browser = strtolower($request->header('User-Agent', 'msie 6.0'));

	$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');

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
$topic_id = $request->variable('topic_id', 0);
$post_msg_id = $request->variable('post_msg_id', 0);
$archive = $request->variable('archive', '.tar');
$mode = request_var('mode', '');
$thumbnail = request_var('t', false);

// Start session management, do not update session page.
$user->session_begin(false);
$auth->acl($user->data);
$user->setup('viewtopic');

if (!$config['allow_attachments'] && !$config['allow_pm_attach'])
{
	send_status_line(404, 'Not Found');
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}

if ($download_id)
{
	// Attachment id (only 1 attachment)
	$sql_where = "attach_id = $download_id";
}
else if ($post_msg_id)
{
	// Post id or private message id (multiple attachments)
	$sql_where = "post_msg_id = $post_msg_id AND is_orphan = 0";
}
else if ($topic_id)
{
	// Topic id (multiple attachments)
	$sql_where = "topic_id = $topic_id AND is_orphan = 0";
}
else
{
	send_status_line(404, 'Not Found');
	trigger_error('NO_ATTACHMENT_SELECTED');
}

$sql = 'SELECT attach_id, post_msg_id, topic_id, in_message, is_orphan, physical_filename, real_filename, extension, mimetype, filesize, filetime
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE $sql_where";
$result = $db->sql_query($sql);

$attachments = $attachment_ids = array();
while ($row = $db->sql_fetchrow($result))
{
	$attachment_id = (int) $row['attach_id'];

	$row['physical_filename'] = utf8_basename($row['physical_filename']);

	$attachment_ids[$attachment_id] = $attachment_id;
	$attachments[$attachment_id] = $row;
}
$db->sql_freeresult($result);

// Make $attachment the first of the attachments we fetched.
$attachment = current($attachments);

if (empty($attachments))
{
	send_status_line(404, 'Not Found');
	trigger_error('ERROR_NO_ATTACHMENT');
}
else if (!download_allowed())
{
	send_status_line(403, 'Forbidden');
	trigger_error($user->lang['LINKAGE_FORBIDDEN']);
}
else if ($download_id)
{
	// sizeof($attachments) == 1

	if (!$attachment['in_message'] && !$config['allow_attachments'] || $attachment['in_message'] && !$config['allow_pm_attach'])
	{
		send_status_line(404, 'Not Found');
		trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
	}

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
			phpbb_download_handle_forum_auth($db, $auth, $attachment['topic_id']);
		}
		else
		{
			// Attachment is in a private message.
			$row['forum_id'] = false;
			phpbb_download_handle_pm_auth($db, $auth, $user->data['user_id'], $attachment['post_msg_id']);
		}

		$extensions = array();
		if (!extension_allowed($row['forum_id'], $attachment['extension'], $extensions))
		{
			send_status_line(404, 'Forbidden');
			trigger_error(sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
		}
	}

	$download_mode = (int) $extensions[$attachment['extension']]['download_mode'];
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
	else if ($display_cat == ATTACHMENT_CATEGORY_NONE && !$attachment['is_orphan'] && !phpbb_http_byte_range($attachment['filesize']))
	{
		// Update download count
		phpbb_increment_downloads($db, $attachment['attach_id']);
	}

	if ($display_cat == ATTACHMENT_CATEGORY_IMAGE && $mode === 'view' && (strpos($attachment['mimetype'], 'image') === 0) && (strpos(strtolower($user->browser), 'msie') !== false) && !phpbb_is_greater_ie_version($user->browser, 7))
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
}
else
{
	// sizeof($attachments) >= 1
	if ($attachment['in_message'])
	{
		phpbb_download_handle_pm_auth($db, $auth, $user->data['user_id'], $attachment['post_msg_id']);
	}
	else
	{
		phpbb_download_handle_forum_auth($db, $auth, $attachment['topic_id']);
	}

	if (!class_exists('compress'))
	{
		require $phpbb_root_path . 'includes/functions_compress.' . $phpEx;
	}

	if (!in_array($archive, compress::methods()))
	{
		$archive = '.tar';
	}

	if ($post_msg_id)
	{
		if ($attachment['in_message'])
		{
			$sql = 'SELECT message_subject AS attach_subject
				FROM ' . PRIVMSGS_TABLE . "
				WHERE msg_id = $post_msg_id";
		}
		else
		{
			$sql = 'SELECT post_subject AS attach_subject, forum_id
				FROM ' . POSTS_TABLE . "
				WHERE post_id = $post_msg_id";
		}
	}
	else
	{
		$sql = 'SELECT topic_title AS attach_subject, forum_id
			FROM ' . TOPICS_TABLE . "
			WHERE topic_id = $topic_id";
	}

	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (empty($row))
	{
		send_status_line(404, 'Not Found');
		trigger_error('ERROR_NO_ATTACHMENT');
	}

	$clean_name = phpbb_download_clean_filename($row['attach_subject']);
	$suffix = '_' . (($post_msg_id) ? $post_msg_id : $topic_id) . '_' . $clean_name;
	$archive_name = 'attachments' . $suffix;

	$store_name = 'att_' . time() . '_' . unique_id();
	$archive_path = "{$phpbb_root_path}store/{$store_name}{$archive}";

	if ($archive === '.zip')
	{
		$compress = new compress_zip('w', $archive_path);
	}
	else
	{
		$compress = new compress_tar('w', $archive_path, $archive);
	}

	$extensions = array();
	$files_added = 0;
	$forum_id = ($attachment['in_message']) ? false : (int) $row['forum_id'];
	$disallowed = array();

	foreach ($attachments as $attach)
	{
		if (!extension_allowed($forum_id, $attach['extension'], $extensions))
		{
			$disallowed[$attach['extension']] = $attach['extension'];
			continue;
		}

		$prefix = '';
		if ($topic_id)
		{
			$prefix = $attach['post_msg_id'] . '_';
		}

		$compress->add_custom_file("{$phpbb_root_path}files/{$attach['physical_filename']}", "{$prefix}{$attach['real_filename']}");
		$files_added++;
	}

	$compress->close();

	if ($files_added)
	{
		phpbb_increment_downloads($db, $attachment_ids);
		$compress->download($store_name, $archive_name);
	}

	unlink($archive_path);

	if (!$files_added)
	{
		// None of the attachments had a valid extension
		$disallowed = implode($user->lang['COMMA_SEPARATOR'], $disallowed);
		send_status_line(404, 'Forbidden');
		trigger_error($user->lang('EXTENSION_DISABLED_AFTER_POSTING', $disallowed));
	}

	file_gc();
}
