<?php
/***************************************************************************
 *                                common.php
 *                            -------------------
 *   begin                : Saturday, Feb 23, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
<<<<<<< common.php
 *   $Id$
=======
 *   $Id$
>>>>>>> 1.24
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
$images['quote'] = "$url_images/quote.gif";
$images['edit'] = "$url_images/edit.gif";
$images['profile'] = "$url_images/profile.gif";
$images['email'] = "$url_images/email.gif";
$images['pmsg'] = "$url_images/pm.gif";
$images['delpost'] = "$url_images/edit.gif";
$images['ip'] = "$url_images/ip_logged.gif";
$images['www'] = "$url_images/www_icon.gif";
$images['icq'] = "$url_images/icq_add.gif";
$images['aim'] = "$url_images/aim.gif";
$images['yim'] = "$url_images/yim.gif";
$images['msnm'] = "$url_images/msnm.gif";
$images['quote'] = "$url_images/quote.gif";
$images['posticon'] = "$url_images/posticon.gif";
$images['folder'] = "$url_images/folder.gif";
$images['new_folder'] = "$url_images/red_folder.gif";
$images['latest_reply'] = "$url_images/latest_reply.gif";

include('includes/template.inc');

include('includes/error.'.$phpEx);
include('includes/sessions.'.$phpEx);
include('includes/auth.'.$phpEx);
include('includes/functions.'.$phpEx);
include('includes/db.'.$phpEx);

// Obtain and encode users IP
//$get_user_ip = ;
$user_ip = encode_ip(($HTTP_X_FORWARDED_FOR) ? $HTTP_X_FORWARDED_FOR : $REMOTE_ADDR);

//
// Setup forum wide options.
// This is also the first DB query/connect
//
$sql = "SELECT *
	FROM ".CONFIG_TABLE."
	WHERE selected = 1";
if(!$result = $db->sql_query($sql))
{
	//
	// Define some basic configuration
	// vars, necessary since we haven't
	// been able to get them from the DB
	//
	$board_config['default_template'] = "Default";
	$board_config['default_timezone'] = 0;
	$board_config['default_dateformat'] = "d M Y H:i";
	$board_config['default_theme'] = 1;
	$board_config['default_lang'] = "english";

	// Our template class hasn't been instantiated
	// so we do it here.
	$template = new Template("templates/Default");

	error_die(SQL_QUERY, "Could not query config information.", __LINE__, __FILE__);
}
else
{
	$config = $db->sql_fetchrow($result);

	$board_config['sitename'] = stripslashes($config['sitename']);
	$board_config['allow_html'] = $config['allow_html'];
	$board_config['allow_bbcode'] = $config['allow_bbcode'];
	$board_config['allow_smilies'] = $config['allow_smilies'];
	$board_config['allow_sig'] = $config['allow_sig'];
	$board_config['allow_namechange'] = $config['allow_namechange'];
	$board_config['allow_avatar_local'] = $config['allow_avatar_local'];
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
}
include('language/lang_'.$board_config['default_lang'].'.'.$phpEx);

?>