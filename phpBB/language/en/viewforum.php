<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : viewforum.php [ English ]
// STARTED   : Sat Dec 16, 2000
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// DO NOT CHANGE
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE 
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang += array(
	'ANNOUNCEMENTS'			=> 'Announcements',

	'LOGIN_NOTIFY_FORUM'	=> 'You have been notified about this forum, please login to view it.',

	'MARK_TOPICS_READ'		=> 'Mark Topics Read',

	'NEW_POSTS_HOT'			=> 'New posts [ Popular ]',
	'NEW_POSTS_LOCKED'		=> 'New posts [ Locked ]',
	'NO_NEW_POSTS_HOT'		=> 'No new posts [ Popular ]',
	'NO_NEW_POSTS_LOCKED'	=> 'No new posts [ Locked ]',

	'POST_FORUM_LOCKED'		=> 'Forum is locked',
	'POST_NEW_TOPIC'		=> 'Post new topic',

	'TOPICS_MARKED'			=> 'The topics for this forum have now been marked read',

	'VIEW_FORUM_TOPIC'		=> '1 Topic',
	'VIEW_FORUM_TOPICS'		=> '%d Topics',
);

?>