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
 ***************************************************************************/

error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
set_magic_quotes_runtime(0); // Disable magic_quotes_runtime

//
// addslashes to vars if magic_quotes_gpc is off
// this is a security precaution to prevent someone
// trying to break out of a SQL statement.
//
if( !get_magic_quotes_gpc() )
{
	if( is_array($HTTP_GET_VARS) )
	{
		while( list($k, $v) = each($HTTP_GET_VARS) )
		{
			if( is_array($HTTP_GET_VARS[$k]) )
			{
				while( list($k2, $v2) = each($HTTP_GET_VARS[$k]) )
				{
					$HTTP_GET_VARS[$k][$k2] = addslashes($v2);
				}
				@reset($HTTP_GET_VARS[$k]);
			}
			else
			{
				$HTTP_GET_VARS[$k] = addslashes($v);
			}
		}
		@reset($HTTP_GET_VARS);
	}

	if( is_array($HTTP_POST_VARS) )
	{
		while( list($k, $v) = each($HTTP_POST_VARS) )
		{
			if( is_array($HTTP_POST_VARS[$k]) )
			{
				while( list($k2, $v2) = each($HTTP_POST_VARS[$k]) )
				{
					$HTTP_POST_VARS[$k][$k2] = addslashes($v2);
				}
				@reset($HTTP_POST_VARS[$k]);
			}
			else
			{
				$HTTP_POST_VARS[$k] = addslashes($v);
			}
		}
		@reset($HTTP_POST_VARS);
	}

	if( is_array($HTTP_COOKIE_VARS) )
	{
		while( list($k, $v) = each($HTTP_COOKIE_VARS) )
		{
			if( is_array($HTTP_COOKIE_VARS[$k]) )
			{
				while( list($k2, $v2) = each($HTTP_COOKIE_VARS[$k]) )
				{
					$HTTP_COOKIE_VARS[$k][$k2] = addslashes($v2);
				}
				@reset($HTTP_COOKIE_VARS[$k]);
			}
			else
			{
				$HTTP_COOKIE_VARS[$k] = addslashes($v);
			}
		}
		@reset($HTTP_COOKIE_VARS);
	}
}

//
// Define some basic configuration arrays this also prevents
// malicious rewriting of language and otherarray values via
// URI params
//
$board_config = Array();
$userdata = Array();
$theme = Array();
$images = Array();
$lang = Array();
$gen_simple_header = FALSE;

@include($phpbb_root_path . 'config.'.$phpEx);

if( !defined("PHPBB_INSTALLED") )
{
	header("Location: install.$phpEx");
}

include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/template.'.$phpEx);
include($phpbb_root_path . 'includes/sessions.'.$phpEx);
include($phpbb_root_path . 'includes/auth.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

//
// Mozilla navigation bar
// Default items that should be valid on all pages.
// Defined here and not in page_header.php so they can be redefined in the code
//
$nav_links['top'] = array ( 
	'url' => append_sid($phpbb_root_dir."index.".$phpEx),
	'title' => sprintf($lang['Forum_Index'], $board_config['sitename'])
);
$nav_links['search'] = array ( 
	'url' => append_sid($phpbb_root_dir."search.".$phpEx),
	'title' => $lang['Search']
);
$nav_links['help'] = array ( 
	'url' => append_sid($phpbb_root_dir."faq.".$phpEx),
	'title' => $lang['FAQ']
);
$nav_links['author'] = array ( 
	'url' => append_sid($phpbb_root_dir."memberlist.".$phpEx),
	'title' => $lang['Memberlist']
);

//
// Obtain and encode users IP
//
if( !empty($HTTP_X_FORWARDED_FOR) )
{
	$client_ip = ( preg_match("/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/", $HTTP_X_FORWARDED_FOR, $ip_list) ) ? $ip_list[0] : $REMOTE_ADDR;
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
	while($row = $db->sql_fetchrow($result))
	{
		$board_config[$row['config_name']] = $row['config_value'];
	}
}

//
// Set some server variables related to the current URL, mostly used for Email
//
if ( !empty($HTTP_SERVER_VARS['HTTPS']) )
{
	$server_protocol = ( !empty($HTTP_SERVER_VARS['HTTPS']) ) ?  ( ( $HTTP_SERVER_VARS['HTTPS'] == "on" ) ? "https://" : "http://" )  : "http://";
}
else if ( !empty($HTTP_ENV_VARS['HTTPS']) )
{
	$server_protocol = ( !empty($HTTP_ENV_VARS['HTTPS']) ) ?  ( ( $HTTP_ENV_VARS['HTTPS'] == "on" ) ? "https://" : "http://" )  : "http://";
}
else
{
	$server_protocol = "http://";
}

if ( !empty($board_config['server_name']) )
{
	$server_name = $board_config['server_name'];
}
else if ( !empty($board_config['cookie_domain']) )
{
	$server_name = $board_config['cookie_domain'];
}
else if( !empty($HTTP_SERVER_VARS['SERVER_NAME']) || !empty($HTTP_ENV_VARS['SERVER_NAME']) )
{
	$server_name = ( !empty($HTTP_SERVER_VARS['SERVER_NAME']) ) ? $HTTP_SERVER_VARS['SERVER_NAME'] : $HTTP_ENV_VARS['SERVER_NAME'];
}
else if( !empty($HTTP_SERVER_VARS['HTTP_HOST']) || !empty($HTTP_ENV_VARS['HTTP_HOST']) )
{
	$server_name = ( !empty($HTTP_SERVER_VARS['HTTP_HOST']) ) ? $HTTP_SERVER_VARS['HTTP_HOST'] : $HTTP_ENV_VARS['HTTP_HOST'];
}
else
{
	$server_name = "";
}

$server_port = ( !empty($board_config['server_port']) && $board_config['server_port'] <> 80 ) ? ':' . $board_config['server_port'] : '';

if ( !empty($HTTP_SERVER_VARS['PHP_SELF']) || !empty($HTTP_ENV_VARS['PHP_SELF']) )
{
	$script_name = ( !empty($HTTP_SERVER_VARS['PHP_SELF']) ) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_ENV_VARS['PHP_SELF'];
}
else if ( !empty($HTTP_SERVER_VARS['SCRIPT_NAME']) || !empty($HTTP_ENV_VARS['SCRIPT_NAME']) )
{
	$script_name = ( !empty($HTTP_SERVER_VARS['SCRIPT_NAME']) ) ? $HTTP_SERVER_VARS['SCRIPT_NAME'] : $HTTP_ENV_VARS['SCRIPT_NAME'];
}
else if ( !empty($HTTP_SERVER_VARS['PATH_INFO']) || !empty($HTTP_ENV_VARS['PATH_INFO']) )
{
	$script_name = ( !empty($HTTP_SERVER_VARS['PATH_INFO']) ) ? $HTTP_SERVER_VARS['PATH_INFO'] : $HTTP_ENV_VARS['PATH_INFO'];
}

$script_url = $server_protocol . $server_name . $server_port . $script_name;

//
// Show 'Board is disabled' message if needed.
//
if( $board_config['board_disable'] && !defined("IN_ADMIN") && !defined("IN_LOGIN") )
{
	message_die(GENERAL_MESSAGE, 'Board_disable', 'Information');
}

?>
