<?php
/***************************************************************************
 *                                  faq.php
 *                            -------------------
 *   begin                : Sunday, Jul 8, 2001
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

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_FAQ, $session_length);
init_userprefs($userdata);
//
// End session management
//

include($phpbb_root_path . 'includes/page_header.'.$phpEx);
include($phpbb_root_path . 'language/faq_' . $board_config['default_lang'] . '.' . $phpEx);

$template->set_filenames(array(
	"body" => "faq_body.tpl")
);

$template->assign_vars(array("L_FAQ" => $lang['FAQ']));

for($i = 0; $i < count($faq); $i++)
{
	$template->assign_block_vars("faqrow", array("FAQ_QUESTION" => $faq[$i][0], "FAQ_ANSWER" => $faq[$i][1]));
}

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>