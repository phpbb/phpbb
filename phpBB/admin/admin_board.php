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
	while( $row = $db->sql_fetchrow($result) )
	{
		$config_name = $row['config_name'];
		$config_value = $row['config_value'];
		$default_config[$config_name] = $config_value;
		
		$new[$config_name] = ( isset($HTTP_POST_VARS[$config_name]) ) ? $HTTP_POST_VARS[$config_name] : $default_config[$config_name];

		if( isset($HTTP_POST_VARS['submit']) )
		{
			if( $config_name == "default_theme" )
			{
				$new[$config_name] = substr($HTTP_POST_VARS['board_style'], strrpos($HTTP_POST_VARS['board_style'], "_") + 1);
			}
			else if( $config_name == "board_template" )
			{
				$new[$config_name] = substr($HTTP_POST_VARS['board_style'], 0, strrpos($HTTP_POST_VARS['board_style'], "_"));
			}

			$sql = "UPDATE " . CONFIG_TABLE . " SET
				config_value = '".$new[$config_name]."'
				WHERE config_name = '$config_name'";
			if( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Failed to update general configuration for $config_name", "", __LINE__, __FILE__, $sql);
			}
		}
	}

	if($HTTP_POST_VARS['submit'])
	{
		message_die(GENERAL_MESSAGE, $lang['Config_updated']);
	}
}

$style_select = style_select($new['board_template'], $new['default_theme'], 'board_style', "../templates");
$lang_select = language_select($new['default_lang'], 'default_lang', "../language");
$timezone_select = tz_select($new['board_timezone'], 'board_timezone');

$html_yes = ($new['allow_html']) ? "checked=\"checked\"" : "";
$html_no = (!$new['allow_html']) ? "checked=\"checked\"" : "";
$bbcode_yes = ($new['allow_bbcode']) ? "checked=\"checked\"" : "";
$bbcode_no = (!$new['allow_bbcode']) ? "checked=\"checked\"" : "";
$activation_yes = ($new['require_activation']) ? "checked=\"checked\"" : "";
$activation_no = (!$new['require_activation']) ? "checked=\"checked\"" : "";
$gzip_yes = ($new['gzip_compress']) ? "checked=\"checked\"" : "";
$gzip_no = (!$new['gzip_compress']) ? "checked=\"checked\"" : "";
$smile_yes = ($new['allow_smilies']) ? "checked=\"checked\"" : "";
$smile_no = (!$new['allow_smilies']) ? "checked=\"checked\"" : "";
$sig_yes = ($new['allow_sig']) ? "checked=\"checked\"" : "";
$sig_no = (!$new['allow_sig']) ? "checked=\"checked\"" : "";
$namechange_yes = ($new['allow_namechange']) ? "checked=\"checked\"" : "";
$namechange_no = (!$new['allow_namechange']) ? "checked=\"checked\"" : "";
$avatars_local_yes = ($new['allow_avatar_local']) ? "checked=\"checked\"" : "";
$avatars_local_no = (!$new['allow_avatar_local']) ? "checked=\"checked\"" : "";
$avatars_remote_yes = ($new['allow_avatar_remote']) ? "checked=\"checked\"" : "";
$avatars_remote_no = (!$new['allow_avatar_remote']) ? "checked=\"checked\"" : "";
$avatars_upload_yes = ($new['allow_avatar_upload']) ? "checked=\"checked\"" : "";
$avatars_upload_no = (!$new['allow_avatar_upload']) ? "checked=\"checked\"" : "";
$smtp_yes = ($new['smtp_delivery']) ? "checked=\"checked\"" : "";
$smtp_no = (!$new['smtp_delivery']) ? "checked=\"checked\"" : "";

$template->set_filenames(array(
	"body" => "admin/admin_config_body.tpl")
);

$template->assign_vars(array(
	"S_CONFIG_ACTION" => append_sid("admin_board.$phpEx"),
	"SITENAME" => $new['sitename'],
	"ACTIVATION_YES" => $activation_yes,
	"ACTIVATION_NO" => $activation_no,
	"FLOOD_INTERVAL" => $new['flood_interval'],
	"TOPICS_PER_PAGE" => $new['topics_per_page'],
	"POSTS_PER_PAGE" => $new['posts_per_page'],
	"HOT_TOPIC" => $new['hot_threshold'],
	"STYLE_SELECT" => $style_select,
	"LANG_SELECT" => $lang_select,
	"L_DATE_FORMAT_EXPLAIN" => $lang['Date_format_explain'],
	"DEFAULT_DATEFORMAT" => $new['default_dateformat'],
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
	"AVATAR_FILESIZE" => $new['avatar_filesize'],
	"AVATAR_MAX_HEIGHT" => $new['avatar_max_height'],
	"AVATAR_MAX_WIDTH" => $new['avatar_max_width'],
	"AVATAR_PATH" => $new['avatar_path'],
	"EMAIL_FROM" => $new['email_from'],
	"EMAIL_SIG" => $new['email_sig'],
	"SMTP_YES" => $smtp_yes,
	"SMTP_NO" => $smtp_no,
	"SMTP_HOST" => $new['smtp_host'])
);

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>
