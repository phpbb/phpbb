<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : download.php
// STARTED   : Thu Apr 10, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if ( defined('IN_PHPBB') )
{
	die('Hacking attempt');
	exit;
}

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

$download_id = (isset($_REQUEST['id'])) ? intval($_REQUEST['id']) : FALSE;
$thumbnail = (isset($_REQUEST['thumb'])) ? intval($_REQUEST['thumb']) : FALSE;

function send_file_to_browser($attachment, $upload_dir, $category)
{
	global $_SERVER, $HTTP_USER_AGENT, $HTTP_SERVER_VARS, $user, $db, $config;

	$filename = ($upload_dir == '') ? $attachment['physical_filename'] : $upload_dir . '/' . $attachment['physical_filename'];

	if (!file_exists($filename))
	{
		trigger_error($user->lang['ERROR_NO_ATTACHMENT'] . '<br /><br />' . sprintf($user->lang['FILE_NOT_FOUND_404'], $filename));
	}

	// Determine the Browser the User is using, because of some nasty incompatibilities.
	// borrowed from phpMyAdmin. :)
	if (!empty($_SERVER['HTTP_USER_AGENT'])) 
	{
		$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
	} 
	else if (!empty($HTTP_SERVER_VARS['HTTP_USER_AGENT'])) 
	{
		$HTTP_USER_AGENT = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
	}
	else if (!isset($HTTP_USER_AGENT))
	{
		$HTTP_USER_AGENT = '';
	}

	if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[2];
		$browser_agent = 'opera';
	} 
	else if (ereg('MSIE ([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'ie';
	} 
	else if (ereg('OmniWeb/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'omniweb';
	} 
	else if (ereg('Netscape([0-9]{1})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'netscape';
	} 
	else if (ereg('Mozilla/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'mozilla';
	} 
	else if (ereg('Konqueror/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'konqueror';
	} 
	else 
	{
		$browser_version = 0;
		$browser_agent = 'other';
	}

	// Correct the mime type - we force application/octetstream for all files, except images
	// Please do not change this, it is a security precaution
	if ($category == NONE_CAT && !strstr($attachment['mimetype'], 'image'))
	{
		$attachment['mimetype'] = ($browser_agent == 'ie' || $browser_agent == 'opera') ? 'application/octetstream' : 'application/octet-stream';
	}

	// Now the tricky part... let's dance
	@ob_end_clean();
	@ini_set('zlib.output_compression', 'Off');
	header('Pragma: public');
	header('Content-Transfer-Encoding: none');

	// Send out the Headers
	header('Content-Type: ' . $attachment['mimetype'] . '; name="' . $attachment['real_filename'] . '"');
	header('Content-Disposition: inline; filename="' . $attachment['real_filename'] . '"');

	// Now send the File Contents to the Browser
	$size = @filesize($filename);
	if ($size)
	{
		header("Content-length: $size");
	}
	readfile($filename);

	exit;
}

// Start session management
$user->start();
$auth->acl($user->data);
$user->setup();

if (!$download_id)
{
	trigger_error('NO_ATTACHMENT_SELECTED');
}

if (!$config['allow_attachments'])
{
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}
	
$sql = 'SELECT *
	FROM ' . ATTACHMENTS_DESC_TABLE . "
	WHERE attach_id = $download_id";
$result = $db->sql_query($sql);

if (!($attachment = $db->sql_fetchrow($result)))
{
	trigger_error('ERROR_NO_ATTACHMENT');
}

// get forum_id for attachment authorization or private message authorization
$authorised = FALSE;

// Additional query, because of more than one attachment assigned to posts and private messages
$sql = 'SELECT a.*, p.forum_id, f.forum_password, f.parent_id
	FROM ' . ATTACHMENTS_TABLE . ' a, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f
	WHERE a.attach_id = ' . $attachment['attach_id'] . '
		AND ((a.post_id = p.post_id AND p.forum_id = f.forum_id) 
			OR a.post_id = 0)';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	if ($row['post_id'] && $auth->acl_get('f_download', $row['forum_id']))
	{
		if ($row['forum_password'])
		{
			// Do something else ... ?
			login_forum_box($row);
		}

		$authorised = TRUE;
		break;
	}
	else
	{
		if ($config['allow_pm_attach'] && ($user->data['user_id'] == $row['user_id_2'] || $user->data['user_id'] == $row['user_id_1']))
		{
			$authorised = TRUE;
			break;
		}
	}
}
$db->sql_freeresult($result);

if (!$authorised)
{
	trigger_error('SORRY_AUTH_VIEW_ATTACH');
}

$extensions = array();
obtain_attach_extensions($extensions);

// disallowed ?
if (!in_array($attachment['extension'], $extensions['_allowed_']))
{
	trigger_error(sprintf($lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
}

$download_mode = intval($extensions[$attachment['extension']]['download_mode']);

if ($thumbnail)
{
	$attachment['physical_filename'] = 'thumbs/t_' . $attachment['physical_filename'];
}

// Update download count
if (!$thumbnail)
{
	$sql = 'UPDATE ' . ATTACHMENTS_DESC_TABLE . ' 
		SET download_count = download_count + 1 
		WHERE attach_id = ' . $attachment['attach_id'];
	$db->sql_query($sql);
}

// Determine the 'presenting'-method
if ($download_mode == PHYSICAL_LINK)
{
	if ($config['use_ftp_upload'] && $config['upload_dir'] == '')
	{
		trigger_error($user->lang['PHYSICAL_DOWNLOAD_NOT_POSSIBLE']);
	}

	redirect($config['upload_dir'] . '/' . $attachment['physical_filename']);
}
else
{
	send_file_to_browser($attachment, $config['upload_dir'], $extensions[$attachment['extension']]['display_cat']);
	exit;
}

?>