<?php
/** 
*
* viewforum [Română]
*
* @package language
* @version $Id: viewforum.php,v 1.18 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: viewforum.php,v 1.18 2008/01/06 03:45:00 www.phpbb.ro (Aliniuz) Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACTIVE_TOPICS'			=> 'Subiecte active',
	'ANNOUNCEMENTS'			=> 'Anunţuri',

	'FORUM_PERMISSIONS'		=> 'Permisiunile forumului',

	'ICON_ANNOUNCEMENT'		=> 'Anunţ',
	'ICON_STICKY'			=> 'Important',

	'LOGIN_NOTIFY_FORUM'	=> 'Aţi fost anunţat despre acest forum, autentificaţi-vă pentru a-l vizualiza.',

	'MARK_TOPICS_READ'		=> 'Marcare subiecte ca fiind citite',

	'NEW_POSTS_HOT'			=> 'Mesaje noi [ Popular ]',
	'NEW_POSTS_LOCKED'		=> 'Mesaje noi [ Închis ]',
	'NO_NEW_POSTS_HOT'		=> 'Nu sunt mesaje noi [ Popular ]',
	'NO_NEW_POSTS_LOCKED'	=> 'Nu sunt mesaje noi [ Închis ]',
	'NO_READ_ACCESS'		=> 'Nu aveţi permisiunile necesare pentru a citi subiectele din cadrul acestui forum.',
	
       'NO_UNREAD_POSTS_HOT'      => 'Nu sunt mesaje necitite [ Popular ]',
       'NO_UNREAD_POSTS_LOCKED'   => 'Nu sunt mesaje necitite [ Închis ]',

	'POST_FORUM_LOCKED'		=> 'Forumul este închis',

	'TOPICS_MARKED'			=> 'Subiectele acestui forum au fost marcate ca fiind citite.',
	
  'UNREAD_POSTS_HOT'      => 'Mesaje necitite [ Popular ]',
  'UNREAD_POSTS_LOCKED'   => 'Mesaje necitite [ Închis ]',

	'VIEW_FORUM'			=> 'Vizualizare forum',
	'VIEW_FORUM_TOPIC'		=> '1 subiect',
	'VIEW_FORUM_TOPICS'		=> '%d subiecte',
));

?>