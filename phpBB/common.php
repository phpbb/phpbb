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

include('config.'.$phpEx);
include('includes/constants.'.$phpEx);

//
// Default variable values - most if not all
// of these have equivalents in a DB table but
// for situations where the DB cannot be read or where
// data is missing this data is used instead
//
//$date_format = "m-d-Y H:i:s"; // American datesformat
$date_format = "D, M d Y h:i:s a"; // European datesformat

$url_images = "images";
$image_quote = "$url_images/quote.gif";

$image_edit = "$url_images/edit.gif";
$image_profile = "$url_images/profile.gif";
$image_email = "$url_images/email.gif";
$image_pmsg = "$url_images/pm.gif";
$image_delpost = "$url_images/edit.gif";

$image_ip = "$url_images/ip_logged.gif";

$image_www = "$url_images/www_icon.gif";
$image_icq = "$url_images/icq_add.gif";
$image_aim = "$url_images/aim.gif";
$image_yim = "$url_images/yim.gif";
$image_msnm = "$url_images/msnm.gif";

// Find Users real IP (if possible)
$user_ip = ($HTTP_X_FORWARDED_FOR) ? $HTTP_X_FORWARDED_FOR : $REMOTE_ADDR;

include('includes/template.inc');

include('includes/error.'.$phpEx);
include('includes/sessions.'.$phpEx);
include('includes/auth.'.$phpEx);
include('includes/functions.'.$phpEx);
include('includes/db.'.$phpEx);

// Initalize to keep safe
$userdata = Array();

// Setup forum wide options.
// This is also the first DB query/connect
$sql = "SELECT *
	FROM ".CONFIG_TABLE."
	WHERE selected = 1";
if(!$result = $db->sql_query($sql))
{
	// Our template class hasn't been instantiated so we do it here.
	$template = new Template("templates/Default");
	error_die(SQL_QUERY, "Could not query config information.", __LINE__, __FILE__);
}
else
{
	$config = $db->sql_fetchrow($result);
	$sitename = stripslashes($config["sitename"]);
	$allow_html = $config["allow_html"];
	$allow_bbcode = $config["allow_bbcode"];
	$allow_sig = $config["allow_sig"];
	$allow_namechange = $config["allow_namechange"];
	$posts_per_page = $config["posts_per_page"];
	$hot_threshold = $config["hot_threshold"];
	$topics_per_page = $config["topics_per_page"];
	$override_user_themes = $config["override_themes"];
	$email_sig = stripslashes($config["email_sig"]);
	$email_from = $config["email_from"];
	$default_lang = $config["default_lang"];
	$require_activation = $config["require_activation"];
	$sys_timezone = $config["system_timezone"];
	$sys_template = $config['sys_template'];
	$sys_lang = $default_lang;
}

include('language/lang_'.$default_lang.'.'.$phpEx);

?>
