<?php
/***************************************************************************
 *                                common.php
 *                            -------------------
 *   begin                : Saturday, Feb 23, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/


/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

//
// Define some basic configuration arrays
// this also prevents malicious rewriting
// of language array values via URI params
//
$board_config = Array();
$userdata = Array();
$theme = Array();
$images = Array();
$lang = Array();

include('config.'.$phpEx);
include('includes/constants.'.$phpEx);

$url_images = "images";
$images['quote'] = "$url_images/icon_quote.gif";
$images['edit'] = "$url_images/icon_edit.gif";
$images['search_icon'] = "$url_images/search_icon.gif";
$images['profile'] = "$url_images/icon_profile.gif";
$images['privmsg'] = "$url_images/icon_pm.gif";
$images['email'] = "$url_images/icon_email.gif";
$images['delpost'] = "$url_images/edit.gif";
$images['ip'] = "$url_images/icon_ip.gif";
$images['www'] = "$url_images/icon_www.gif";
$images['icq'] = "$url_images/icon_icq_add.gif";
$images['aim'] = "$url_images/icon_aim.gif";
$images['yim'] = "$url_images/icon_yim.gif";
$images['msnm'] = "$url_images/icon_msnm.gif";
$images['posticon'] = "$url_images/posticon.gif";
$images['folder'] = "$url_images/folder.gif";
$images['new_folder'] = "$url_images/red_folder.gif";
$images['latest_reply'] = "$url_images/latest_reply.gif";
$images['locked_folder'] = "$url_images/folder_lock.gif";

include('includes/template.inc');

include('includes/error.'.$phpEx);
include('includes/message.'.$phpEx);
include('includes/sessions.'.$phpEx);
include('includes/auth.'.$phpEx);
include('includes/functions.'.$phpEx);
include('includes/db.'.$phpEx);

//
// Obtain and encode users IP
//
if(!empty($HTTP_CLIENT_IP))
{
	if(ereg("[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+", $HTTP_CLIENT_IP))
	{
		$client_ip = $HTTP_CLIENT_IP; 
	}
}
else if(!empty($HTTP_X_FORWARDED_FOR))
{
	if(ereg(",", $HTTP_X_FORWARDED_FOR))
	{
		list($client_ip) = explode(",", $HTTP_X_FORWARDED_FOR);
	}
	else
	{
		$client_ip = $HTTP_X_FORWARDED_FOR;
	}
}
else if(!empty($HTTP_VIA))
{
	if(ereg("[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+", $HTTP_VIA))
	{
		$client_ip = $HTTP_VIA;
	}
}
else if(!empty($HTTP_PROXY_USER))
{
	if(ereg("[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+", $HTTP_PROXY_USER))
	{
		$client_ip = $HTTP_PROXY_USER;
	}
}
else
{
	$client_ip = $REMOTE_ADDR;
}
$user_ip = encode_ip($client_ip);

//
// Setup forum wide options, if this fails
// then we output a CRITICAL_ERROR since
// basic forum information is not available
//
$sql = "SELECT *
	FROM " . CONFIG_TABLE;
if(!$result = $db->sql_query($sql))
{
	message_die(CRITICAL_ERROR, "Could not query config information", "", __LINE__, __FILE__, $sql);
}
else
{
/*
	while($row = $db->sql_fetchrow($result))
	{
		$board_config[$row['config_var_name']] = stripslashes($row['config_var_value']);
	}
*/

	$config = $db->sql_fetchrow($result);

	$board_config['board_disable'] = $config['board_disable'];
	$board_config['sitename'] = stripslashes($config['sitename']);
	$board_config['allow_html'] = $config['allow_html'];
	$board_config['allow_bbcode'] = $config['allow_bbcode'];
	$board_config['allow_smilies'] = $config['allow_smilies'];
	$board_config['allow_sig'] = $config['allow_sig'];
	$board_config['allow_namechange'] = $config['allow_namechange'];
	$board_config['allow_avatar_local'] = $config['allow_avatar_local'];
	$board_config['allow_avatar_remote'] = $config['allow_avatar_local'];
	$board_config['allow_avatar_upload'] = $config['allow_avatar_upload'];
	$board_config['require_activation'] = $config['require_activation'];
	$board_config['override_user_themes'] = $config['override_themes'];
	$board_config['posts_per_page'] = $config['posts_per_page'];
	$board_config['topics_per_page'] = $config['topics_per_page'];
	$board_config['default_theme'] = $config['default_theme'];
	$board_config['default_dateformat'] = stripslashes($config['default_dateformat']);
	$board_config['default_template'] = stripslashes($config['sys_template']);
	$board_config['default_timezone'] = $config['system_timezone'];
	$board_config['default_lang'] = stripslashes($config['default_lang']);
	$board_config['board_email'] = stripslashes(str_replace("<br />", "\n", $config['email_sig']));
	$board_config['board_email_from'] = stripslashes($config['email_from']);
	$board_config['flood_interval'] = $config['flood_interval'];
	$board_config['avatar_filesize'] = $config['avatar_filesize'];
	$board_config['avatar_max_width'] = $config['avatar_max_width'];
	$board_config['avatar_max_height'] = $config['avatar_max_height'];
	$board_config['avatar_path'] = $config['avatar_path'];
	$board_config['prune_enable'] = $config['prune_enable'];
	$board_config['gzip_compress'] = $config['gzip_compress'];
	$board_config['smtp_delivery'] = $config['smtp_delivery'];
	$board_config['smtp_host'] = $config['smtp_host'];
}

include('language/lang_' . $board_config['default_lang'] . '.'.$phpEx);

if($board_config['board_disable'])
{
	message_die(GENERAL_MESSAGE, $lang['Board_disable'], $lang['Information']);
}

?>