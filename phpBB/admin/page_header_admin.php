<?php
/***************************************************************************
 *                           page_header_admin.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: page_header_admin.php,v 1.1 2010/10/10 15:05:22 orynider Exp $
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
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
}

define('HEADER_INC', true);

//
// gzip_compression
//
$do_gzip_compress = FALSE;
if ( $board_config['gzip_compress'] )
{
	$phpver = phpversion();

	$useragent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT');

	if ( $phpver >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) )
	{
		if ( extension_loaded('zlib') )
		{
			ob_start('ob_gzhandler');
		}
	}
	else if ( $phpver > '4.0' )
	{
		if ( strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') )
		{
			if ( extension_loaded('zlib') )
			{
				$do_gzip_compress = TRUE;
				ob_start();
				ob_implicit_flush(0);

				header('Content-Encoding: gzip');
			}
		}
	}
}

$phpbb_version_parts = explode('.', PHPBB_VERSION, 3);
$phpbb_major = $phpbb_version_parts[0] . '.' . $phpbb_version_parts[1];

$default_lang = ($user->data['user_lang']) ? $user->data['user_lang'] : $board_config['default_lang'];
$server_name = !empty($board_config['server_name']) ? preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['server_name'])) : 'localhost';
$server_protocol = ($board_config['cookie_secure'] ) ? 'https://' : 'http://';
$server_port = (($board_config['server_port']) && ($board_config['server_port'] <> 80)) ? ':' . trim($board_config['server_port']) . '/' : '/';
$script_name_phpbb = preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['script_path'])) . '/';		
$server_url = $server_protocol . str_replace("//", "/", $server_name . $server_port . $server_name . '/'); //On some server the slash is not added and this trick will fix it	
$corrected_url = $server_protocol . $server_name . $server_port . $script_name_phpbb;
$board_url = $server_url . $script_name_phpbb;
$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $corrected_url;

// Send a proper content-language to the output
$user_lang = !empty($user->lang['USER_LANG']) ? $user->lang['USER_LANG'] : $user->encode_lang($user->lang_name);

if (!defined('TEMPLATE_ROOT_PATH'))
{
	define('TEMPLATE_ROOT_PATH', $phpbb_root_path.'templates/'.$theme['template_name'].'/');
}

if(isset($mx_page) && is_object($mx_page))
{
	$page_title = $mx_page->page_title;
}
//
// Parse and show the overall header.
//
$template->set_filenames(array(
	'header' => ( !isset($gen_simple_header) ) ? 'admin/page_header.html' : 'admin/simple_header.html')
);

// Format Timezone. We are unable to use array_pop here, because of PHP3 compatibility
$l_timezone = explode('.', $board_config['board_timezone']);
$l_timezone = (count($l_timezone) > 1 && $l_timezone[count($l_timezone)-1] != 0) ? $lang[sprintf('%.1f', $board_config['board_timezone'])] : $lang[number_format($board_config['board_timezone'])];

//
// The following assigns all _common_ variables that may be used at any point
// in a template. Note that all URL's should be wrapped in append_sid, as
// should all S_x_ACTIONS for forms.
//
$template->assign_vars(array(
	'SITENAME' 						=> $board_config['sitename'],
	'SITE_DESCRIPTION' 			=> $board_config['site_desc'],
	'PAGE_TITLE' 					=> isset($page_title) ? $page_title : $lang['Admin'],
	'SCRIPT_NAME' 					=> str_replace('.' . $phpEx, '', basename(__FILE__)),	
	'L_ADMIN' 							=> $lang['Admin'], 
	'L_INDEX' 							=> sprintf($lang['Forum_Index'], $board_config['sitename']),
	'L_FAQ' 							=> $lang['FAQ'],

	'U_INDEX' 							=> append_sid('../index.'.$phpEx),

	'S_TIMEZONE' 					=> sprintf($lang['All_times'], $l_timezone),
	'S_LOGIN_ACTION' 			=> append_sid('../login.'.$phpEx),
	'S_JUMPBOX_ACTION' 		=> append_sid('../viewforum.'.$phpEx),
	'S_CURRENT_TIME' 			=> sprintf($lang['Current_time'], create_date($board_config['default_dateformat'], time(), $board_config['board_timezone'])), 
	'S_CONTENT_DIRECTION' 	=> $lang['DIRECTION'], 
	'S_CONTENT_ENCODING' 	=> $lang['ENCODING'], 
	'S_CONTENT_DIR_LEFT' 		=> $lang['LEFT'], 
	'S_CONTENT_DIR_RIGHT' 	=> $lang['RIGHT'], 
	
	'T_HEAD_STYLESHEET' 		=> isset($theme['head_stylesheet']) ? $theme['head_stylesheet'] : 'admin/admin.css',
	'T_GECKO_STYLESHEET' => 'gecko.css',
	'T_BODY_BACKGROUND' 	=> $theme['body_background'],
	'T_BODY_BGCOLOR' 			=> '#'.$theme['body_bgcolor'],
	'T_BODY_TEXT' => '#'.$theme['body_text'],
	'T_BODY_LINK' => '#'.$theme['body_link'],
	'T_BODY_VLINK' => '#'.$theme['body_vlink'],
	'T_BODY_ALINK' => '#'.$theme['body_alink'],
	'T_BODY_HLINK' => '#'.$theme['body_hlink'],
	'T_TR_COLOR1' => '#'.$theme['tr_color1'],
	'T_TR_COLOR2' => '#'.$theme['tr_color2'],
	'T_TR_COLOR3' => '#'.$theme['tr_color3'],
	'T_TR_CLASS1' => $theme['tr_class1'],
	'T_TR_CLASS2' => $theme['tr_class2'],
	'T_TR_CLASS3' => $theme['tr_class3'],
	'T_TH_COLOR1' => '#'.$theme['th_color1'],
	'T_TH_COLOR2' => '#'.$theme['th_color2'],
	'T_TH_COLOR3' => '#'.$theme['th_color3'],
	'T_TH_CLASS1' => $theme['th_class1'],
	'T_TH_CLASS2' => $theme['th_class2'],
	'T_TH_CLASS3' => $theme['th_class3'],
	'T_TD_COLOR1' => '#'.$theme['td_color1'],
	'T_TD_COLOR2' => '#'.$theme['td_color2'],
	'T_TD_COLOR3' => '#'.$theme['td_color3'],
	'T_TD_CLASS1' => $theme['td_class1'],
	'T_TD_CLASS2' => $theme['td_class2'],
	'T_TD_CLASS3' => $theme['td_class3'],
	'T_FONTFACE1' => $theme['fontface1'],
	'T_FONTFACE2' => $theme['fontface2'],
	'T_FONTFACE3' => $theme['fontface3'],
	'T_FONTSIZE1' => $theme['fontsize1'],
	'T_FONTSIZE2' => $theme['fontsize2'],
	'T_FONTSIZE3' => $theme['fontsize3'],
	'T_FONTCOLOR1' => '#'.$theme['fontcolor1'],
	'T_FONTCOLOR2' => '#'.$theme['fontcolor2'],
	'T_FONTCOLOR3' => '#'.$theme['fontcolor3'],
	'T_SPAN_CLASS1' => $theme['span_class1'],
	'T_SPAN_CLASS2' => $theme['span_class2'],
	'T_SPAN_CLASS3' => $theme['span_class3'],
	
	'ROOT_PATH'			=> $web_path,
	'FULL_SITE_PATH'	=> $web_path,
	'CMS_PAGE_HOME'		=> $board_url,
	'BOARD_URL'			=> $board_url,
	'PHPBB_VERSION'		=> PHPBB_VERSION,
	'PHPBB_MAJOR'		=> $phpbb_major,
	'S_COOKIE_NOTICE'	=> !empty($board_config['cookie_name']),
	
	'T_STYLESHEET_LINK'		=> "{$web_path}templates/" . rawurlencode($theme['template_name'] ? $theme['template_name'] : str_replace('.css', '', $theme['head_stylesheet'])) . '/theme/stylesheet.css',
	'T_STYLESHEET_LANG_LINK'=> "{$web_path}templates/" . rawurlencode($theme['template_name'] ? $theme['template_name'] : str_replace('.css', '', $theme['head_stylesheet'])) . '/theme/images/lang_' . $default_lang . '/stylesheet.css',
	'T_FONT_AWESOME_LINK'	=> "{$web_path}assets/css/font-awesome.min.css",
	'T_FONT_IONIC_LINK'			=> "{$web_path}assets/css/ionicons.min.css",
	'T_JQUERY_LINK'			=> "{$web_path}assets/javascript/jquery.min.js?assets_version=" . $phpbb_major,
	'S_ALLOW_CDN'			=> true,	
	
	'T_THEME_NAME'				=> rawurlencode($theme['template_name']),
	'T_THEME_LANG_NAME'		=> $user->data['user_lang'],
	'T_TEMPLATE_NAME'			=> $theme['template_name'],
	'T_SUPER_TEMPLATE_NAME'	=> rawurlencode($theme['template_name']),
	'TEMPLATE_ROOT_PATH' => TEMPLATE_ROOT_PATH,
	'U_PHPBB_ROOT_PATH' => PHPBB_URL,
	)
);

// Work around for "current" Apache 2 + PHP module which seems to not
// cope with private cache control setting
if (!empty($_SERVER['SERVER_SOFTWARE']) && strstr($_SERVER['SERVER_SOFTWARE'], 'Apache/2'))
{
	header ('Cache-Control: no-cache, pre-check=0, post-check=0');
}
else
{
	header ('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
}
header ('Expires: 0');
header ('Pragma: no-cache');

$template->pparse('header');

?>
