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
//
$board_config = Array();
$userdata = Array();
$theme = Array();
$images = Array();

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
$images['latest_reply'] = "$url_images/latest_reply.gif";

// Find Users real IP (if possible)
$user_ip = ($HTTP_X_FORWARDED_FOR) ? $HTTP_X_FORWARDED_FOR : $REMOTE_ADDR;

include('includes/template.inc');

include('includes/error.'.$phpEx);
include('includes/sessions.'.$phpEx);
include('includes/auth.'.$phpEx);
include('includes/functions.'.$phpEx);
include('includes/db.'.$phpEx);

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
	$board_config['allow_sig'] = $config['allow_sig'];
	$board_config['allow_namechange'] = $config['allow_namechange'];
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
}

include('language/lang_'.$board_config['default_lang'].'.'.$phpEx);

?>