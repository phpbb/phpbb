<?php
/***************************************************************************
 *                            admin_announce.php
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
 ***************************************************************************/

define("IN_ADMIN", true);

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['General']['Announcements']   = $filename;

	return;
}

//
// Load default header
//
$phpbb_root_dir = "./../";
$no_page_header = TRUE;
require('pagestart.inc');

message_die(GENERAL_MESSAGE, "Announcements are not yet implemented.  Click <a href=\"" . append_sid("admin_forumauth." . $phpEx . "?f=-1&submit=submit") . "\">here</a> to view the global announcement permissions. Click <a href=\"" . append_sid("./../viewforum." . $phpEx . "?f=-1") . "\">here</a> to view the global announcments forum separately.", __LINE__, __FILE__, "", "");
?>