<?php
/***************************************************************************
 *                              admin_board.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

if($setmodules == 1)
{
	$file = basename(__FILE__);
	$module['General']['Configuration'] = "$file?mode=config";
	return;
}

//
// Let's set the root dir for phpBB
//
$phpbb_root_dir = "./../";

//
// Include required files, get $phpEx and check permissions
//
require('pagestart.inc');

$sql = "SELECT *
	FROM " . CONFIG_TABLE;
if(!$result = $db->sql_query($sql))
{
	message_die(CRITICAL_ERROR, "Could not query config information in admin_board", "", __LINE__, __FILE__, $sql);
}
else
{
/*
	while($row = $db->sql_fetchrow($result))
	{
		$board_config[$row['config_var_name']] = stripslashes($row['config_var_value']);
	}
*/

	$default_config = $db->sql_fetchrow($result);
}

$sitename = (isset($HTTP_POST_VARS['sitename'])) ? $HTTP_POST_VARS['sitename'] : $default_config['sitename'];
$require_activation = (isset($HTTP_POST_VARS['require_activation'])) ? $HTTP_POST_VARS['require_activation'] : $default_config['require_activation'];
$flood_interval = (isset($HTTP_POST_VARS['flood_interval'])) ? $HTTP_POST_VARS['flood_interval'] : $default_config['flood_interval'];
$topics_per_page = (isset($HTTP_POST_VARS['topics_per_page'])) ? $HTTP_POST_VARS['topics_per_page'] : $default_config['topics_per_page'];
$posts_per_page = (isset($HTTP_POST_VARS['posts_per_page'])) ? $HTTP_POST_VARS['posts_per_page'] : $default_config['posts_per_page'];
$hot_topic = (isset($HTTP_POST_VARS['hot_topic'])) ? $HTTP_POST_VARS['hot_topic'] : $default_config['hot_threshold'];
$selected_template = (isset($HTTP_POST_VARS['template'])) ? $HTTP_POST_VARS['template'] : $default_config['sys_template'];
$template_select = template_select($selected_template, "../templates");
$theme = (isset($HTTP_POST_VARS['theme'])) ? $HTTP_POST_VARS['theme'] : $default_config['default_theme'];
$theme_select = theme_select($theme);
$language = (isset($HTTP_POST_VARS['language'])) ? $HTTP_POST_VARS['language'] : $default_config['default_lang'];
$lang_select = language_select($language, "../language");
$timezone = (isset($HTTP_POST_VARS['timezone'])) ? intval($HTTP_POST_VARS['timezone']) : $default_config['system_timezone'];
$timezone_select = tz_select($timezone);
$date_format = (isset($HTTP_POST_VARS['date_format'])) ? $HTTP_POST_VARS['date_format'] : $default_config['default_dateformat'];
$gzip = (isset($HTTP_POST_VARS['gzip'])) ? $HTTP_POST_VARS['gzip'] : $default_config['gzip_compress'];
$prune = (isset($HTTP_POST_VARS['prune'])) ? $HTTP_POST_VARS['prune'] : $default_config['prune_enable'];
$allow_html = (isset($HTTP_POST_VARS['allow_html'])) ? $HTTP_POST_VARS['allow_html'] : $default_config['allow_html'];
$allow_bbcode = (isset($HTTP_POST_VARS['allow_bbcode'])) ? $HTTP_POST_VARS['allow_bbcode'] : $default_config['allow_bbcode'];
$allow_smile = (isset($HTTP_POST_VARS['allow_smile'])) ? $HTTP_POST_VARS['allow_smile'] : $default_config['allow_smilies'];
$allow_sig = (isset($HTTP_POST_VARS['allow_sig'])) ? $HTTP_POST_VARS['allow_sig'] : $default_config['allow_sig'];
$allow_namechange = (isset($HTTP_POST_VARS['allow_namechange'])) ? $HTTP_POST_VARS['allow_namechange'] : $default_config['allow_namechange'];
$allow_avatars_local = (isset($HTTP_POST_VARS['allow_avatars_local'])) ? $HTTP_POST_VARS['allow_avatars_local'] : $default_config['allow_avatar_local'];
$allow_avatars_remote = (isset($HTTP_POST_VARS['allow_avatars_remote'])) ? $HTTP_POST_VARS['allow_avatars_remote'] : $default_config['allow_avatar_remote'];
$allow_avatars_upload = (isset($HTTP_POST_VARS['allow_avatars_upload'])) ? $HTTP_POST_VARS['allow_avatars_upload'] : $default_config['allow_avatar_upload'];
$avatar_filesize = (isset($HTTP_POST_VARS['avatar_filesize'])) ? $HTTP_POST_VARS['avatar_filesize'] : $default_config['avatar_filesize'];
$avatar_height = (isset($HTTP_POST_VARS['avatar_height'])) ? $HTTP_POST_VARS['avatar_height'] : $default_config['avatar_max_height'];
$avatar_width = (isset($HTTP_POST_VARS['avatar_width'])) ? $HTTP_POST_VARS['avatar_width'] : $default_config['avatar_max_width'];
$avatar_path = (isset($HTTP_POST_VARS['avatar_path'])) ? $HTTP_POST_VARS['avatar_path'] : $default_config['avatar_path'];
$admin_email = (isset($HTTP_POST_VARS['admin_email'])) ? $HTTP_POST_VARS['admin_email'] : $default_config['board_email_from'];
$email_sig = (isset($HTTP_POST_VARS['email_sig'])) ? $HTTP_POST_VARS['email_sig'] : $default_config['board_email'];
$use_smtp = (isset($HTTP_POST_VARS['use_smtp'])) ? $HTTP_POST_VARS['use_smtp'] : $default_config['smtp_delivery'];
$smtp_server = (isset($HTTP_POST_VARS['smtp_server'])) ? $HTTP_POST_VARS['smtp_server'] : $default_config['smtp_host'];

$html_yes = ($allow_html) ? "checked=\"checked\"" : "";
$html_no = (!$allow_html) ? "checked=\"checked\"" : "";
$bbcode_yes = ($allow_bbcode) ? "checked=\"checked\"" : "";
$bbcode_no = (!$allow_bbcode) ? "checked=\"checked\"" : "";
$activation_yes = ($require_activation) ? "checked=\"checked\"" : "";
$activation_no = (!$require_activation) ? "checked=\"checked\"" : "";
$gzip_yes = ($gzip) ? "checked=\"checked\"" : "";
$gzip_no = (!$gzip) ? "checked=\"checked\"" : "";
$smile_yes = ($allow_smile) ? "checked=\"checked\"" : "";
$smile_no = (!$allow_smile) ? "checked=\"checked\"" : "";
$sig_yes = ($allow_sig) ? "checked=\"checked\"" : "";
$sig_no = (!$allow_sig) ? "checked=\"checked\"" : "";
$namechange_yes = ($allow_namechange) ? "checked=\"checked\"" : "";
$namechange_no = (!$allow_namechange) ? "checked=\"checked\"" : "";
$avatars_local_yes = ($allow_avatars_local) ? "checked=\"checked\"" : "";
$avatars_local_no = (!$allow_avatars_local) ? "checked=\"checked\"" : "";
$avatars_remote_yes = ($allow_avatars_remote) ? "checked=\"checked\"" : "";
$avatars_remote_no = (!$allow_avatars_remote) ? "checked=\"checked\"" : "";
$avatars_upload_yes = ($allow_avatars_upload) ? "checked=\"checked\"" : "";
$avatars_upload_no = (!$allow_avatars_upload) ? "checked=\"checked\"" : "";
$smtp_yes = ($use_smtp) ? "checked=\"checked\"" : "";
$smtp_no = (!$use_smtp) ? "checked=\"checked\"" : "";

if($HTTP_POST_VARS['submit'])
{
	$sql = "UPDATE " . CONFIG_TABLE . " SET
		sitename = '$sitename',
		allow_html = $allow_html,
		allow_bbcode = $allow_bbcode,
		allow_smilies = $allow_smile,
		allow_sig = $allow_sig,
		allow_namechange = $allow_namechange,
		allow_avatar_local = $allow_avatars_local,
		allow_avatar_remote = $allow_avatars_remote,
		allow_avatar_upload = $allow_avatars_upload,
		posts_per_page = $posts_per_page,
		topics_per_page = $topics_per_page,
		hot_threshold = $hot_topic,
		email_sig = '$email_sig',
		email_from = '$admin_email',
		smtp_delivery = $use_smtp,
		smtp_host = '$smtp_server',
		require_activation = $require_activation,
		flood_interval = $flood_interval,
		avatar_filesize = $avatar_filesize,
		avatar_max_width = $avatar_width,
		avatar_max_height = $avatar_height,
		avatar_path = '$avatar_path',
		default_theme = $theme,
		default_lang = '$language',
		default_dateformat = '$date_format',
		system_timezone = $timezone,
		sys_template = '$selected_template',
		gzip_compress = $gzip, 
		prune_enable = $prune"; 

	if( !$db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Failed to update general configuration", "", __LINE__, __FILE__, $sql);
	}

	message_die(GENERAL_MESSAGE, $lang['Config_updated']);
}


$template->set_filenames(array(
	"body" => "admin/admin_config_body.tpl")
);

$template->assign_vars(array(
	"S_CONFIG_ACTION" => append_sid("admin_board.$phpEx"),
	"SITENAME" => $sitename,
	"ACTIVATION_YES" => $activation_yes,
	"ACTIVATION_NO" => $activation_no,
	"FLOOD_INTERVAL" => $flood_interval,
	"TOPICS_PER_PAGE" => $topics_per_page,
	"POSTS_PER_PAGE" => $posts_per_page,
	"HOT_TOPIC" => $hot_topic,
	"TEMPLATE_SELECT" => $template_select,
	"THEME_SELECT" => $theme_select,
	"LANG_SELECT" => $lang_select,
	"L_DATE_FORMAT_EXPLAIN" => $lang['Date_format_explain'],
	"DATE_FORMAT" => $date_format,
	"TIMEZONE_SELECT" => $timezone_select,
	"GZIP_YES" => $gzip_yes,
	"GZIP_NO" => $gzip_no,
	"HTML_YES" => $html_yes,
	"HTML_NO" => $html_no,
	"BBCODE_YES" => $bbcode_yes,
	"BBCODE_NO" => $bbcode_no,
	"SMILE_YES" => $smile_yes,
	"SMILE_NO" => $smile_no,
	"SIG_YES" => $sig_yes,
	"SIG_NO" => $sig_no,
	"NAMECHANGE_YES" => $namechange_yes,
	"NAMECHANGE_NO" => $namechange_no,
	"AVATARS_LOCAL_YES" => $avatars_local_yes,
	"AVATARS_LOCAL_NO" => $avatars_local_no,
	"AVATARS_REMOTE_YES" => $avatars_remote_yes,
	"AVATARS_REMOTE_NO" => $avatars_remote_no,
	"AVATARS_UPLOAD_YES" => $avatars_upload_yes,
	"AVATARS_UPLOAD_NO" => $avatars_upload_no,
	"AVATAR_FILESIZE" => $avatar_filesize,
	"AVATAR_HEIGHT" => $avatar_height,
	"AVATAR_WIDTH" => $avatar_width,
	"AVATAR_PATH" => $avatar_path,
	"ADMIN_EMAIL" => $admin_email,
	"EMAIL_SIG" => $email_sig,
	"SMTP_YES" => $smtp_yes,
	"SMTP_NO" => $smtp_no,
	"SMTP_SERVER" => $smtp_server)
);

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>