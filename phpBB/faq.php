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
 *   This program is free software; you can redistribute it and/or modified
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

include('extension.inc');
include('common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_FAQ, $session_length);
init_userprefs($userdata);
//
// End session management
//

include('includes/page_header.'.$phpEx); 

include('includes/page_tail.'.$phpEx); 

?>