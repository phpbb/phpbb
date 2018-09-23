<?php
/***************************************************************************
 *                               pagestart.php
 *                            -------------------
 *   begin                : Thursday, Aug 2, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: pagestart.php,v 1.1 2010/10/10 15:05:22 orynider Exp $
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

if (!defined('IN_PHPBB'))
{
	die("Hacking attempt");
}

define('IN_ADMIN', true);
// Include files
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = $user->session_pagestart($user_ip, PAGE_SEARCH);
$user->set_lang($user->lang, $user->help, 'common');
$lang = &$user->lang;
//$user->_init_userprefs($user->data);
init_userprefs($user->data);
//
// End session management
//

if (!$userdata['session_logged_in'])
{
	//redirect(append_sid("login.$phpEx?redirect=admin/index.$phpEx", true));
}
else if ($userdata['user_level'] != ADMIN)
{
	//message_die(GENERAL_MESSAGE, $lang['Not_admin']);
}

if ($_GET['sid'] != $userdata['session_id'])
{
	//redirect("index.$phpEx?sid=" . $userdata['session_id']);
}

if (!$userdata['session_admin'])
{
	//redirect(append_sid("login.$phpEx?redirect=admin/index.$phpEx&admin=1", true));
}

if (empty($no_page_header))
{
	// Not including the pageheader can be neccesarry if META tags are
	// needed in the calling script.
	include('./page_header_admin.'.$phpEx);
}

?>