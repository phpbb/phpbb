<?php
/***************************************************************************
 *                              page_header.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
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

define(HEADER_INC, TRUE);

//
// gzip_compression
//
$do_gzip_compress = FALSE;
if($board_config['gzip_compress'])
{
	$phpver = phpversion();

	if($phpver >= "4.0.4pl1")
	{
		if(extension_loaded("zlib"))
		{
			ob_start("ob_gzhandler");
		}
	}
	else if($phpver > "4.0")
	{
		if(strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip'))
		{ 
			$do_gzip_compress = TRUE;
			ob_start();
			ob_implicit_flush(0); 

			header("Content-Encoding: gzip"); 
		}
	}
}

if(empty($template_header))
{
	$template_header = "admin/page_header.tpl";
}
$template->set_filenames(array(
	"header" => $template_header) 
);

//
// Do timezone text output
//
if($board_config['default_timezone'] < 0)
{
	$s_timezone = $lang['All_times'] . " " .$lang['GMT'] . " - " . (-$board_config['default_timezone']) . " " . $lang['Hours'];
}
else if($board_config['default_timezone'] == 0)
{
	$s_timezone = $lang['All_times'] . " " . $lang['GMT'];
}
else
{
	$s_timezone = $lang['All_times'] . " " . $lang['GMT'] ." + " . $board_config['default_timezone'] . " " . $lang['Hours'];
}

//
// The following assigns all _common_ variables that may be used at any point
// in a template. Note that all URL's should be wrapped in append_sid, as
// should all S_x_ACTIONS for forms.
//
$template->assign_vars(array(
	"SITENAME" => $board_config['sitename'],
	"PAGE_TITLE" => $page_title,
	"META_INFO" => $meta_tags,

	"L_USERNAME" => $lang['Username'],
	"L_PASSWORD" => $lang['Password'],
	"L_INDEX" => $lang['Forum_Index'],
	"L_REGISTER" => $lang['Register'],
	"L_PROFILE" => $lang['Profile'],
	"L_SEARCH" => $lang['Search'],
	"L_PRIVATEMSGS" => $lang['Private_msgs'],
	"L_MEMBERLIST" => $lang['Memberlist'],
	"L_FAQ" => $lang['FAQ'],
	"L_USERGROUPS" => $lang['Usergroups'],
	"L_FORUM" => $lang['Forum'],
	"L_TOPICS" => $lang['Topics'],
	"L_REPLIES" => $lang['Replies'],
	"L_VIEWS" => $lang['Views'],
	"L_POSTS" => $lang['Posts'],
	"L_LASTPOST" => $lang['Last_Post'],
	"L_MODERATOR" => $lang['Moderator'],
	"L_NONEWPOSTS" => $lang['No_new_posts'],
	"L_NEWPOSTS" => $lang['New_posts'],
	"L_POSTED" => $lang['Posted'],
	"L_JOINED" => $lang['Joined'],
	"L_AUTHOR" => $lang['Author'],
	"L_MESSAGE" => $lang['Message'],
	"L_BY" => $lang['by'],

	"U_INDEX" => append_sid("../index.".$phpEx),

	"S_TIMEZONE" => $s_timezone,
	"S_LOGIN_ACTION" => append_sid("../login.$phpEx"),
	"S_JUMPBOX_ACTION" => append_sid("../viewforum.$phpEx"),
	"S_CURRENT_TIME" => create_date($board_config['default_dateformat'], time(), $board_config['default_timezone']),

	"T_HEAD_STYLESHEET" => $theme['head_stylesheet'],
	"T_BODY_BACKGROUND" => $theme['body_background'],
	"T_BODY_BGCOLOR" => "#".$theme['body_bgcolor'],
	"T_BODY_TEXT" => "#".$theme['body_text'],
	"T_BODY_LINK" => "#".$theme['body_link'],
	"T_BODY_VLINK" => "#".$theme['body_vlink'],
	"T_BODY_ALINK" => "#".$theme['body_alink'],
	"T_BODY_HLINK" => "#".$theme['body_hlink'],
	"T_TR_COLOR1" => "#".$theme['tr_color1'],
	"T_TR_COLOR2" => "#".$theme['tr_color2'],
	"T_TR_COLOR3" => "#".$theme['tr_color3'],
	"T_TH_COLOR1" => "#".$theme['th_color1'],
	"T_TH_COLOR2" => "#".$theme['th_color2'],
	"T_TH_COLOR3" => "#".$theme['th_color3'],
	"T_TD_COLOR1" => "#".$theme['td_color1'],
	"T_TD_COLOR2" => "#".$theme['td_color2'],
	"T_TD_COLOR3" => "#".$theme['td_color3'],
	"T_FONTFACE1" => $theme['fontface1'],
	"T_FONTFACE2" => $theme['fontface2'],
	"T_FONTFACE3" => $theme['fontface3'],
	"T_FONTSIZE1" => $theme['fontsize1'],
	"T_FONTSIZE2" => $theme['fontsize2'],
	"T_FONTSIZE3" => $theme['fontsize3'],
	"T_FONTCOLOR1" => "#".$theme['fontcolor1'],
	"T_FONTCOLOR2" => "#".$theme['fontcolor2'],
	"T_FONTCOLOR3" => "#".$theme['fontcolor3'],
	"T_IMG1" => $theme['img1'],
	"T_IMG2" => $theme['img2'],
	"T_IMG3" => $theme['img3'],
	"T_IMG4" => $theme['img4'])
);

header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

$template->pparse("header");

?>